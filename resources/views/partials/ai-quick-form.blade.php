{{-- Поле «спросить консультанта» + меняющиеся подсказки под ним --}}
<form id="aiQuickForm" class="mx-auto" style="max-width:640px;">
    <div class="input-group input-group-lg">
        <input type="text" id="aiQuickInput" class="form-control"
               placeholder="Что вас беспокоит? Спросите фармацевта-консультанта"
               style="border-radius:24px 0 0 24px;border:none;" autocomplete="off">
        <button class="btn" type="submit"
                style="background:#a6d175;color:#fff;border-radius:0 24px 24px 0;padding-inline:1.75rem;">
            Спросить
        </button>
    </div>

    <div id="aiHints" class="d-flex flex-wrap justify-content-center gap-2 mt-3"></div>
</form>
