{{-- Polls the Edit page every 30s; the page's autosaveDraft() persists drafts. --}}
<div wire:poll.30s="autosaveDraft"></div>
