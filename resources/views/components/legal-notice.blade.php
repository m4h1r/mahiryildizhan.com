{{-- Scaffolded legal text — must be reviewed/finalised by the owner or a lawyer
     before relying on it (E11). Shown as an info status, not conversion copy. --}}
<x-status-message type="info" class="not-prose mt-10">
    @if (app()->getLocale() === 'en')
        This text is a starting template and should be reviewed by the site owner or a lawyer
        before it is relied upon.
    @else
        Bu metin bir başlangıç şablonudur; güvenilmeden önce site sahibi veya bir avukat
        tarafından gözden geçirilmelidir.
    @endif
</x-status-message>
