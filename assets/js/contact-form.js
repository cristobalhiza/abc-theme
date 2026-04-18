/**
 * Lógica del formulario de contacto con Alpine.js
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('contactForm', () => ({
        formData: {
            name: '',
            email: '',
            phone: '',
            course: [],
            isCompany: false,
            turnstileToken: ''
        },
        courseOptions: [
            { value: 'Clase B', label: 'Licencia Clase B' },
            { value: 'Clase A2', label: 'Profesional A2' },
            { value: 'Clase A4', label: 'Profesional A4' },
            { value: 'Clase D', label: 'Operador Grúa Clase D (Próximamente)', comingSoon: true }
        ],
        errors: { phone: false, course: false, turnstile: false },
        isLoading: false,
        isSuccess: false,
        serverError: '',
        _turnstileWidgetId: null,

        init() {
            // Escuchar preselección de cursos desde eventos externos
            window.addEventListener('preselect-course', (e) => {
                const requested = e.detail;
                if (!requested) return;

                const normalize = (str) => String(str).toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
                
                const requestedItems = Array.isArray(requested) ? requested : [requested];
                const selectedValues = [];

                requestedItems.forEach(item => {
                    const normalizedRequested = normalize(item);
                    const option = this.courseOptions.find(opt => {
                        const v = normalize(opt.value);
                        const l = normalize(opt.label);
                        return normalizedRequested.includes(v) || v.includes(normalizedRequested) ||
                               normalizedRequested.includes(l) || l.includes(normalizedRequested);
                    });
                    if (option && !selectedValues.includes(option.value)) {
                        selectedValues.push(option.value);
                    }
                });

                if (selectedValues.length > 0) {
                    this.formData.course = selectedValues;
                    this.errors.course = false;
                }
            });

            this._renderTurnstile();
        },

        _renderTurnstile() {
            const container = this.$refs.turnstileContainer;
            if (!container) return;

            const doRender = () => {
                if (this._turnstileWidgetId !== null) return;
                
                // Usamos la variable global pasada por wp_localize_script
                const sitekey = window.abcContactConfig?.turnstileSiteKey || '';
                
                if (!sitekey) {
                    console.error('Turnstile Site Key no encontrada.');
                    return;
                }

                this._turnstileWidgetId = turnstile.render(container, {
                    sitekey: sitekey,
                    callback: (token) => {
                        this.formData.turnstileToken = token;
                        this.errors.turnstile = false;
                    },
                    'expired-callback': () => {
                        this.formData.turnstileToken = '';
                    },
                    'error-callback': () => {
                        this.formData.turnstileToken = '';
                    },
                    theme: 'light'
                });
            };

            if (typeof turnstile !== 'undefined') {
                doRender();
            } else {
                const interval = setInterval(() => {
                    if (typeof turnstile !== 'undefined') {
                        clearInterval(interval);
                        doRender();
                    }
                }, 200);
            }
        },

        getCourseLabel(val) {
            const opt = this.courseOptions.find(o => o.value === val);
            return opt ? opt.label : val;
        },

        validatePhone() {
            const phoneRegex = /^[+]?[\d\s-]{8,15}$/;
            this.errors.phone = this.formData.phone !== '' && !phoneRegex.test(this.formData.phone);
        },

        async submitForm() {
            if (this.formData.course.length === 0) {
                this.errors.course = true;
            }
            if (!this.formData.turnstileToken) {
                this.errors.turnstile = true;
            }
            if (this.errors.phone || this.errors.course || this.errors.turnstile) return;
            
            this.isLoading = true;
            this.serverError = '';

            try {
                const response = await fetch('/wp-json/abc/v1/contact', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.formData)
                });

                const result = await response.json();

                if (response.ok) {
                    this.isSuccess = true;
                } else {
                    this.serverError = result.message || 'Ocurrió un error al enviar el mensaje. Intenta nuevamente.';
                    this._resetTurnstile();
                }
            } catch (error) {
                this.serverError = 'Error de conexión. Por favor, verifica tu internet.';
                this._resetTurnstile();
            } finally {
                this.isLoading = false;
            }
        },

        _resetTurnstile() {
            this.formData.turnstileToken = '';
            if (this._turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
                turnstile.reset(this._turnstileWidgetId);
            }
        },

        resetForm() {
            this.formData = { name: '', email: '', phone: '', course: [], isCompany: false, turnstileToken: '' };
            this.isSuccess = false;
            this.serverError = '';
            this.errors = { phone: false, course: false, turnstile: false };
            this._resetTurnstile();
        }
    }))
});
