{{-- 
    Reusable Modal Component
    Usage: @include('components.admin.modal', [
        'id' => 'delete-modal',
        'title' => 'Confirm Delete',
        'size' => 'sm', // sm, md, lg
    ])
    
    Define the body content using @slot('body') in a component or inline content
--}}

<div class="modal-backdrop" id="{{ $id }}">
    <div class="modal-container modal-{{ $size ?? 'md' }}">
        <div class="modal-header">
            <h3 class="modal-title">{{ $title ?? 'Modal' }}</h3>
            <button class="modal-close-btn" onclick="Modal.close('{{ $id }}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            {{ $slot ?? '' }}
        </div>
        @if(isset($footer))
        <div class="modal-footer">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
