@php
    $images = $record->getMedia('gallery');
@endphp

@if ($images->isEmpty())
    <div style="padding: 1.5rem; text-align: center; color: #6b7280;">
        <p>No hay fotografías subidas para esta sección.</p>
    </div>
@else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1rem; padding: 0.5rem 0;">
        @foreach ($images as $img)
            <div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
                <div style="aspect-ratio: 1; overflow: hidden; background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                    <img
                        src="{{ $img->getUrl('webp') ?: $img->getUrl() }}"
                        alt="{{ $img->getCustomProperty('alt') ?: $img->file_name }}"
                        style="width: 100%; height: 100%; object-fit: cover;"
                    >
                </div>
                <div style="padding: 0.5rem; font-size: 0.75rem; color: #4b5563; word-break: break-all; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; gap: 0.25rem;">
                    <span title="{{ $img->file_name }}">{{ \Illuminate\Support\Str::limit($img->file_name, 14) }}</span>
                    <button
                        type="button"
                        wire:click="deleteMedia({{ $img->id }})"
                        wire:confirm="¿Seguro que deseas eliminar esta fotografía?"
                        style="color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.75rem; padding: 0 0.25rem; font-weight: 600;"
                        title="Eliminar foto"
                    >
                        ✕
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
