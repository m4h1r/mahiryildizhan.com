<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Adage;
use App\Models\BloodType;
use App\Models\Comment;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\EyeColor;
use App\Models\Gender;
use App\Models\HairColor;
use App\Models\Income;
use App\Models\IncomeSource;
use App\Models\IncomeType;
use App\Models\Interaction;
use App\Models\InteractionType;
use App\Models\Link;
use App\Models\Node;
use App\Models\NodeConnection;
use App\Models\Person;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostLanguage;
use App\Models\PurchaseItem;
use App\Models\Setting;
use App\Models\Stakeholder;
use App\Models\Subscriber;
use App\Models\TimelineEvent;
use App\Models\TodoItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards against factory/migration drift (D6): every domain model must have a
 * working factory that satisfies the schema's constraints (unique, FK, not-null).
 */
class ModelFactoriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<class-string>
     */
    public static function modelClasses(): array
    {
        return [
            User::class,
            Gender::class,
            EyeColor::class,
            HairColor::class,
            BloodType::class,
            InteractionType::class,
            ExpenseType::class,
            IncomeSource::class,
            IncomeType::class,
            Currency::class,
            PostCategory::class,
            PostLanguage::class,
            Person::class,
            Interaction::class,
            Post::class,
            Comment::class,
            Expense::class,
            Income::class,
            PurchaseItem::class,
            TodoItem::class,
            Stakeholder::class,
            Node::class,
            NodeConnection::class,
            Subscriber::class,
            Adage::class,
            TimelineEvent::class,
            Link::class,
            Setting::class,
            ActivityLog::class,
        ];
    }

    public function test_every_domain_model_factory_creates_a_valid_record(): void
    {
        foreach (self::modelClasses() as $modelClass) {
            $model = $modelClass::factory()->create();

            $this->assertNotNull($model->id, "{$modelClass}::factory() did not persist a record.");
            $this->assertDatabaseHas($model->getTable(), ['id' => $model->id]);
        }
    }
}
