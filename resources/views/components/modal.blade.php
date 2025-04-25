<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                {{ $body }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $dismissName }}</button>
@if($needForm == 'true')
                <form action="{{ $action }}" method="POST" id="{{ $formId }}">
                    @csrf
                    @method($method ?? 'POST')
                    <button type="submit" class="{{ $buttonClass }}">{{ $buttonText }}</button>
                </form>
@elseif($needExtForm == 'true')
<button type="submit" form="{{ $extFormId }}" class="{{ $buttonClass }}">{{ $buttonText }}</button>
@endif
            </div>
        </div>
    </div>
</div>
