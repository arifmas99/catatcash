import Alpine from 'alpinejs';

Alpine.data('pinKeypad', (options = {}) => ({
    mode: options.mode === 'confirm' ? 'confirm' : 'single',
    locked: Boolean(options.locked),
    remaining: Number(options.remaining) || 0,
    step: 1,
    value: '',
    firstPin: '',

    init() {
        if (this.locked && this.remaining > 0) {
            const tick = setInterval(() => {
                this.remaining -= 1;

                if (this.remaining <= 0) {
                    clearInterval(tick);
                    window.location.reload();
                }
            }, 1000);
        }
    },

    get lockedLabel() {
        const minutes = String(Math.floor(this.remaining / 60)).padStart(2, '0');
        const seconds = String(this.remaining % 60).padStart(2, '0');

        return `${minutes}:${seconds}`;
    },

    get stepLabel() {
        if (this.mode !== 'confirm') {
            return '';
        }

        return this.step === 1
            ? 'Langkah 1 dari 2 — Masukkan PIN baru'
            : 'Langkah 2 dari 2 — Ulangi PIN untuk konfirmasi';
    },

    press(digit) {
        if (this.locked || this.value.length >= 6) {
            return;
        }

        this.value += String(digit);

        if (this.value.length === 6) {
            this.complete();
        }
    },

    backspace() {
        if (this.locked) {
            return;
        }

        this.value = this.value.slice(0, -1);
    },

    complete() {
        if (this.mode === 'single') {
            this.$refs.pin.value = this.value;
            this.$refs.form.submit();

            return;
        }

        if (this.step === 1) {
            this.firstPin = this.value;
            this.value = '';
            this.step = 2;

            return;
        }

        this.$refs.pin.value = this.firstPin;
        this.$refs.confirm.value = this.value;
        this.$refs.form.submit();
    },
}));

window.Alpine = Alpine;

Alpine.start();
