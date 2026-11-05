<x-app :title="$title.' | Rexalmajestad'" :noindex="! $isPublished">
    <section class="content-page page-prose" aria-labelledby="legal-title">
        <h1 id="legal-title">{{ $title }}</h1>
        @if ($document)
            <div class="legal-document">{{ $document->body }}</div>
        @else
            <p role="status">Este documento está pendiente de completar y revisión profesional antes de publicar la web.</p>
        @endif
    </section>
</x-app>
