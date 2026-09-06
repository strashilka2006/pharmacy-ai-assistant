{{-- Модалка ИИ-консультанта. Порт блока из index.php. --}}
<div class="modal fade" id="aiChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:18px;">
            <div class="modal-header" style="background:#f8fef3;border-bottom:1px solid #e0ebc6;">
                <h5 class="modal-title">Фармацевт-консультант</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="aiChatMessages"
                 style="height:60vh;overflow-y:auto;display:flex;flex-direction:column;gap:14px;"></div>

            <div class="modal-footer" style="background:#fbfdf7;">
                <form id="aiModalForm" class="d-flex w-100 gap-2">
                    <input type="text" id="aiModalInput" class="form-control"
                           placeholder="Ваш вопрос" autocomplete="off">
                    <button class="btn" style="background:#a6d175;color:#fff;">Отправить</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.AI_CHAT_URL = @json(route('ai.chat'));
</script>
@endpush
