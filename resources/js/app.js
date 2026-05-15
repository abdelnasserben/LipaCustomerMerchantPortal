document.addEventListener('alpine:init', () => {
    window.Alpine.data('otpInput', (initial = '') => ({
        value: (initial || '').replace(/\D/g, '').slice(0, 6),

        init() {
            this.syncToInputs();
        },

        syncToInputs() {
            for (let i = 0; i < 6; i++) {
                const el = this.$refs['d' + i];
                if (el) el.value = this.value[i] ?? '';
            }
        },

        rebuild() {
            let v = '';
            for (let i = 0; i < 6; i++) {
                const el = this.$refs['d' + i];
                v += (el && el.value ? el.value.replace(/\D/g, '').slice(0, 1) : '');
            }
            this.value = v;
        },

        focus(i) {
            const el = this.$refs['d' + i];
            if (el) { el.focus(); el.select(); }
        },

        onInput(e, i) {
            const digit = e.target.value.replace(/\D/g, '').slice(-1);
            e.target.value = digit;
            this.rebuild();
            if (digit && i < 5) this.focus(i + 1);
        },

        onKeydown(e, i) {
            if (e.key === 'Backspace') {
                if (e.target.value) {
                    e.target.value = '';
                    this.rebuild();
                } else if (i > 0) {
                    e.preventDefault();
                    const prev = this.$refs['d' + (i - 1)];
                    if (prev) { prev.value = ''; prev.focus(); }
                    this.rebuild();
                }
                return;
            }
            if (e.key === 'ArrowLeft' && i > 0) { e.preventDefault(); this.focus(i - 1); return; }
            if (e.key === 'ArrowRight' && i < 5) { e.preventDefault(); this.focus(i + 1); return; }
            if (e.key === 'Delete') {
                e.target.value = '';
                this.rebuild();
            }
        },

        onPaste(e) {
            const text = (e.clipboardData || window.clipboardData).getData('text') || '';
            const digits = text.replace(/\D/g, '').slice(0, 6);
            if (!digits) return;
            this.value = digits;
            this.syncToInputs();
            const next = Math.min(digits.length, 5);
            this.focus(next);
        },
    }));
});
