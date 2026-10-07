import jsQR from 'jsqr';

/**
 * Check-in scanner controller (Alpine).
 * The camera only decodes QR content; every decision is made by the server.
 */
window.checkinController = function (verifyUrl, csrfToken, i18n) {
    const t = (key) => (i18n && i18n[key]) || key;

    return {
        scanning: false,
        cameraSupported: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
        cameraError: '',
        cameraMessage: '',
        manualToken: '',
        manualPlaceholder: 'shamrashamra.com/i/…',
        result: {
            status: 'idle',
            message: t('scanning'),
            label: null,
            entitlement: null,
            guestCount: null,
            checkedAt: null,
            checkedBy: null,
        },
        stream: null,
        canvas: null,
        ctx: null,
        rafId: null,
        lastCode: '',
        lastCodeAt: 0,
        busy: false,

        async start() {
            this.cameraError = '';
            this.cameraMessage = '';

            if (!this.cameraSupported) {
                this.cameraError = t('unsupported');
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1280 } },
                    audio: false,
                });
            } catch (err) {
                if (err && (err.name === 'NotAllowedError' || err.name === 'SecurityError')) {
                    this.cameraError = t('permission');
                } else if (err && (err.name === 'NotFoundError' || err.name === 'OverconstrainedError' || err.name === 'NotReadableError')) {
                    this.cameraError = t('unavailable');
                } else {
                    this.cameraError = t('unsupported');
                }
                return;
            }

            const video = this.$refs.video;
            video.srcObject = this.stream;
            await video.play().catch(() => {});

            this.scanning = true;
            this.cameraMessage = t('scanning');
            this.loop(video);
        },

        stop() {
            this.scanning = false;
            if (this.rafId) cancelAnimationFrame(this.rafId);
            if (this.stream) {
                this.stream.getTracks().forEach((track) => track.stop());
                this.stream = null;
            }
            if (this.$refs.video) this.$refs.video.srcObject = null;
        },

        loop(video) {
            if (!this.scanning) return;

            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                if (!this.canvas) {
                    this.canvas = document.createElement('canvas');
                    this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
                }

                const w = video.videoWidth;
                const h = video.videoHeight;

                if (w && h) {
                    this.canvas.width = w;
                    this.canvas.height = h;
                    this.ctx.drawImage(video, 0, 0, w, h);
                    const image = this.ctx.getImageData(0, 0, w, h);
                    const code = jsQR(image.data, w, h, { inversionAttempts: 'dontInvert' });

                    if (code && code.data) {
                        const now = Date.now();
                        if (code.data !== this.lastCode || now - this.lastCodeAt > 4000) {
                            this.lastCode = code.data;
                            this.lastCodeAt = now;
                            this.verify(code.data, 'qr');
                        }
                    }
                }
            }

            this.rafId = requestAnimationFrame(() => this.loop(video));
        },

        async submitManual() {
            const token = (this.manualToken || '').trim();
            if (!token) return;
            await this.verify(token, 'manual');
            this.manualToken = '';
        },

        async verify(token, method) {
            if (this.busy) return;
            this.busy = true;

            try {
                const response = await fetch(verifyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ token, method }),
                });

                if (response.status === 429) {
                    this.setResult('invalid', t('throttled'));
                    return;
                }

                const data = await response.json();

                if (!response.ok) {
                    this.setResult('invalid', t('network'));
                    return;
                }

                this.setResult(data.status, data.message, data);
            } catch (err) {
                this.setResult('invalid', t('network'));
            } finally {
                this.busy = false;
            }
        },

        setResult(status, message, data = {}) {
            this.result = {
                status,
                message,
                label: data.label ?? null,
                entitlement: data.entitlement ?? null,
                guestCount: data.guest_count ?? null,
                checkedAt: data.checked_in_at ? new Date(data.checked_in_at).toLocaleTimeString() : null,
                checkedBy: data.checked_in_by ?? null,
            };
        },
    };
};
