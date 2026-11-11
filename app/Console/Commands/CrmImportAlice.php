<?php

namespace App\Console\Commands;

use App\Models\Person;
use App\Models\PersonAssessment;
use App\Services\Crm\AliceCardMapper;
use App\Services\Crm\FaceStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * One-off import of Alice's five-layer person analyses into person cards.
 *
 * The Alice panel lives on the Tailscale network, which the production server
 * may not reach. Then: run `--url … --export=file.json` on a machine that can
 * reach it, copy the file to the server, and run `--from=file.json` there.
 */
class CrmImportAlice extends Command
{
    protected $signature = 'crm:import-alice
        {--url= : Alice panel base URL (default: config crm.alice_panel_url)}
        {--from= : Import from a JSON file written by --export instead of the panel}
        {--export= : Only fetch from the panel and write a JSON file (no database changes)}
        {--no-create : Do not create people that cannot be matched}
        {--dry-run : Show what would happen, change nothing}';

    protected $description = 'Alice panelindeki 5 katmanlı kişi analizlerini kişi kartlarına aktar';

    public function handle(AliceCardMapper $mapper, FaceStorage $faces): int
    {
        try {
            $bundle = $this->option('from') ? $this->readFile((string) $this->option('from')) : $this->fetch();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($export = $this->option('export')) {
            File::put($export, json_encode($bundle, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->info(count($bundle['people']).' kişi '.$export.' dosyasına yazıldı.');

            return self::SUCCESS;
        }

        $dry = (bool) $this->option('dry-run');
        $rows = [];

        DB::beginTransaction();
        try {
            foreach ($bundle['people'] as $payload) {
                $rows[] = $this->importOne($payload, $bundle['photos'][$payload['meta']['slug'] ?? ''] ?? null, $mapper, $faces, $dry);
            }
            $dry ? DB::rollBack() : DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(['Alice', 'Kişi', 'Eşleşme', 'Analiz', 'Foto'], $rows);
        $this->info($dry ? 'Deneme çalıştırması: hiçbir şey kaydedilmedi.' : 'İçe aktarma tamamlandı.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private function importOne(array $payload, ?string $photo, AliceCardMapper $mapper, FaceStorage $faces, bool $dry): array
    {
        $meta = (array) ($payload['meta'] ?? []);
        $slug = (string) ($meta['slug'] ?? '');
        $fullName = trim((string) ($meta['kisi'] ?? $slug));

        [$person, $match] = $this->match($meta, $fullName);

        if (! $person) {
            if ($this->option('no-create')) {
                return [$slug, $fullName, 'bulunamadı (atlandı)', '—', '—'];
            }
            $parts = preg_split('/\s+/u', $fullName) ?: [$fullName];
            $surname = count($parts) > 1 ? array_pop($parts) : '';
            $person = Person::query()->create(['name' => implode(' ', $parts), 'surname' => $surname]);
            $match = 'yeni kişi';
        }

        // Fill blanks only — never overwrite what was entered by hand.
        $fill = ['alice_slug' => $person->alice_slug ?: $slug];
        foreach ($mapper->personHints($payload) as $field => $value) {
            if ($value !== null && blank($person->{$field})) {
                $fill[$field] = $value;
            }
        }
        $person->update($fill);

        $data = $mapper->assessment($payload);
        $existing = PersonAssessment::query()
            ->where('person_id', $person->id)
            ->where('source', 'import')
            ->whereDate('assessed_at', $data['assessed_at'])
            ->first();
        $existing ? $existing->update($data) : $person->assessments()->create($data);
        $assessmentNote = $existing ? 'güncellendi' : 'eklendi';

        $photoNote = '—';
        if ($photo !== null && ! $person->faces()->where('source', 'import')->exists()) {
            if ($dry) {
                $photoNote = 'eklenecek';
            } else {
                $binary = base64_decode($photo, true);
                if ($binary !== false) {
                    $faces->create($person, ['source' => 'import'], null, $binary);
                    $photoNote = 'eklendi';
                }
            }
        }

        return [$slug, $person->fullName().' (#'.$person->id.')', $match, $assessmentNote, $photoNote];
    }

    /**
     * Match order: panel_id → people.id, alice_slug, then exact name + surname.
     *
     * @param  array<string, mixed>  $meta
     * @return array{0: ?Person, 1: string}
     */
    private function match(array $meta, string $fullName): array
    {
        if (! empty($meta['panel_id']) && ($p = Person::query()->find((int) $meta['panel_id']))) {
            return [$p, 'panel_id'];
        }
        if (! empty($meta['slug']) && ($p = Person::query()->where('alice_slug', $meta['slug'])->first())) {
            return [$p, 'alice_slug'];
        }

        $parts = preg_split('/\s+/u', $fullName) ?: [];
        if (count($parts) > 1) {
            $surname = array_pop($parts);
            $p = Person::query()
                ->where('name', implode(' ', $parts))
                ->where('surname', $surname)
                ->get();
            if ($p->count() === 1) {
                return [$p->first(), 'ad soyad'];
            }
        }

        return [null, ''];
    }

    /**
     * @return array{people: array<int, array<string, mixed>>, photos: array<string, string>}
     */
    private function fetch(): array
    {
        $base = rtrim((string) ($this->option('url') ?: config('crm.alice_panel_url')), '/');
        $http = Http::timeout(15)->connectTimeout(5)->acceptJson();

        $list = $http->get($base.'/api/kisiler')->throw()->json();
        if (! is_array($list)) {
            throw new \RuntimeException('Alice paneli beklenmeyen bir yanıt döndü: '.$base.'/api/kisiler');
        }

        $people = [];
        $photos = [];
        $bar = $this->output->createProgressBar(count($list));
        foreach ($list as $item) {
            $slug = (string) ($item['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $people[] = $http->get($base.'/api/kisiler/'.rawurlencode($slug))->throw()->json();
            if (! empty($item['foto_url'])) {
                $res = Http::timeout(15)->get($base.$item['foto_url']);
                if ($res->successful() && str_starts_with((string) $res->header('Content-Type'), 'image/')) {
                    $photos[$slug] = base64_encode($res->body());
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        return ['people' => $people, 'photos' => $photos];
    }

    /**
     * @return array{people: array<int, array<string, mixed>>, photos: array<string, string>}
     */
    private function readFile(string $path): array
    {
        if (! File::exists($path)) {
            throw new \RuntimeException('Dosya bulunamadı: '.$path);
        }
        $data = json_decode(File::get($path), true);
        if (! is_array($data) || ! isset($data['people'])) {
            throw new \RuntimeException('Geçersiz dosya: "people" listesi yok.');
        }

        return ['people' => (array) $data['people'], 'photos' => (array) ($data['photos'] ?? [])];
    }
}
