(function () {
    const DICCIONARIO_GLOBAL = {
        es: {
            lema: 'Amor que cambia vidas',
            volver_inicio: 'Volver al inicio',
            volver: 'Volver',
            configuracion: 'Configuración',
            titulo_config: 'Configuración',
            subtitulo_config: 'Personaliza tus preferencias en MonPet',
            perfil_profesional: 'Perfil Profesional',
            subtitulo_perfil: 'Gestiona tu información profesional y brinda un mejor cuidado a las mascotas',
            editar_perfil: 'Editar perfil',
            mascotas_atendidas: 'Mascotas atendidas',
            reportes_consultas: 'Reportes / consultas',
            mascotas_asignadas: 'Mascotas asignadas',
            sobre_mi: 'Sobre mí',
            horario_atencion: 'Mi horario de atención',
            credenciales: 'Credenciales',
            actividad_reciente: 'Actividad reciente',
            buscador_placeholder: 'Buscar mascota, historia o refugio...',
            preferencias: 'Preferencias',
            tema_interfaz: 'Tema de la interfaz:',
            tema_claro: 'Claro',
            tema_oscuro: 'Oscuro',
            idioma: 'Idioma:',
            zona_horaria: 'Zona horaria',
            notificaciones: 'Notificaciones',
            subtitulo_notif: 'Recibe alertas importantes sobre MonPet',
            notif_1: 'Nuevas solicitudes de revisión médica',
            notif_2: 'Recordatorio de jornadas y campañas',
            notif_3: 'Mensajes de usuarios',
            notif_4: 'Actualizaciones del sistema',
            seguridad: 'Seguridad',
            seg_pass_titulo: 'Cambiar contraseña',
            seg_pass_desc: 'Mantén tu cuenta segura con una nueva contraseña.',
            btn_cambiar: 'Cambiar',
            seg_verif_titulo: 'Verificar identidad',
            seg_verif_desc: 'Confirma tu identidad con tu correo electrónico.',
            btn_verificar: 'Verificar',
            seg_logout_titulo: 'Cerrar sesión',
            seg_logout_desc: 'Sal de tu cuenta de forma segura.',
            btn_cerrar_sesion: 'Cerrar sesión',
            btn_guardar: 'Guardar cambios'
        },
        en: {
            lema: 'Love that changes lives',
            volver_inicio: 'Back to home',
            volver: 'Back',
            configuracion: 'Settings',
            titulo_config: 'Settings',
            subtitulo_config: 'Customize your preferences in MonPet',
            perfil_profesional: 'Professional Profile',
            subtitulo_perfil: 'Manage your professional info and provide better care for pets',
            editar_perfil: 'Edit profile',
            mascotas_atendidas: 'Pets attended',
            reportes_consultas: 'Reports / consultations',
            mascotas_asignadas: 'Assigned pets',
            sobre_mi: 'About me',
            horario_atencion: 'My business hours',
            credenciales: 'Credentials',
            actividad_reciente: 'Recent activity',
            buscador_placeholder: 'Search pet, story or shelter...',
            preferencias: 'Preferences',
            tema_interfaz: 'Interface theme:',
            tema_claro: 'Light',
            tema_oscuro: 'Dark',
            idioma: 'Language:',
            zona_horaria: 'Time zone',
            notificaciones: 'Notifications',
            subtitulo_notif: 'Receive important alerts about MonPet',
            notif_1: 'New medical review requests',
            notif_2: 'Campaign and event reminders',
            notif_3: 'User messages',
            notif_4: 'System updates',
            seguridad: 'Security',
            seg_pass_titulo: 'Change password',
            seg_pass_desc: 'Keep your account secure with a new password.',
            btn_cambiar: 'Change',
            seg_verif_titulo: 'Verify identity',
            seg_verif_desc: 'Confirm your identity with your email.',
            btn_verificar: 'Verify',
            seg_logout_titulo: 'Log out',
            seg_logout_desc: 'Sign out of your account safely.',
            btn_cerrar_sesion: 'Log out',
            btn_guardar: 'Save changes'
        }
    };

    function obtenerClavesPerfil() {
        const perfilId = (document.body && document.body.getAttribute('data-perfil-id')) || 'invitado';
        return {
            claveTema: `monpet_tema_${perfilId}`,
            claveIdioma: `monpet_idioma_${perfilId}`
        };
    }

    function aplicarTemaGlobal(tema) {
        const valor = (tema === 'oscuro') ? 'oscuro' : 'claro';
        document.documentElement.setAttribute('data-tema', valor);
        if (document.body) {
            document.body.setAttribute('data-tema', valor);
        }

        document.querySelectorAll('.selector-temas .opcion-tema').forEach((tarjeta) => {
            const esEsta = tarjeta.getAttribute('data-tema-opcion') === valor;
            tarjeta.classList.toggle('activo', esEsta);

            const radio = tarjeta.querySelector('input[type="radio"]');
            if (radio) radio.checked = esEsta;

            const badge = tarjeta.querySelector('.badge-check');
            if (esEsta && !badge) {
                const nuevoBadge = document.createElement('span');
                nuevoBadge.className = 'badge-check';
                nuevoBadge.innerHTML = '<i class="fa-solid fa-check"></i>';
                tarjeta.prepend(nuevoBadge);
            } else if (!esEsta && badge) {
                badge.remove();
            }
        });

        const { claveTema } = obtenerClavesPerfil();
        try {
            localStorage.setItem(claveTema, valor);
        } catch (err) {}
    }

    function aplicarIdiomaGlobal(idioma) {
        const lang = (idioma === 'en') ? 'en' : 'es';
        const textos = DICCIONARIO_GLOBAL[lang];

        document.documentElement.setAttribute('lang', lang);

        const selectIdioma = document.getElementById('idioma');
        if (selectIdioma && selectIdioma.value !== lang) {
            selectIdioma.value = lang;
        }

        document.querySelectorAll('[data-i18n]').forEach((el) => {
            const clave = el.getAttribute('data-i18n');
            if (textos[clave]) {
                el.textContent = textos[clave];
            }
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
            const clave = el.getAttribute('data-i18n-placeholder');
            if (textos[clave]) {
                el.setAttribute('placeholder', textos[clave]);
            }
        });

        const buscador = document.getElementById('input-buscador');
        if (buscador && !buscador.hasAttribute('data-i18n-placeholder')) {
            buscador.placeholder = textos.buscador_placeholder;
        }

        const { claveIdioma } = obtenerClavesPerfil();
        try {
            localStorage.setItem(claveIdioma, lang);
        } catch (err) {}
    }

    window.MonPetPreferencias = {
        aplicarTema: aplicarTemaGlobal,
        aplicarIdioma: aplicarIdiomaGlobal
    };

    document.addEventListener('DOMContentLoaded', () => {
        const huboPost = document.body && document.body.getAttribute('data-hubo-post') === '1';
        const temaServer = (document.body && document.body.getAttribute('data-tema-server')) || 'claro';
        const idiomaServer = (document.body && document.body.getAttribute('data-idioma-server')) || 'es';
        const { claveTema, claveIdioma } = obtenerClavesPerfil();

        let temaInicial = temaServer;
        let idiomaInicial = idiomaServer;

        if (!huboPost) {
            try {
                const temaGuardado = localStorage.getItem(claveTema);
                const idiomaGuardado = localStorage.getItem(claveIdioma);
                if (temaGuardado) temaInicial = temaGuardado;
                if (idiomaGuardado) idiomaInicial = idiomaGuardado;
            } catch (err) {}
        }

        aplicarTemaGlobal(temaInicial);
        aplicarIdiomaGlobal(idiomaInicial);

        document.querySelectorAll('.selector-temas .opcion-tema').forEach((tarjeta) => {
            tarjeta.addEventListener('click', () => {
                aplicarTemaGlobal(tarjeta.getAttribute('data-tema-opcion'));
            });
        });

        const selectIdioma = document.getElementById('idioma');
        if (selectIdioma) {
            selectIdioma.addEventListener('change', (ev) => {
                aplicarIdiomaGlobal(ev.target.value);
            });
        }

        const formConfig = document.getElementById('form-configuracion');
        if (formConfig) {
            formConfig.addEventListener('submit', () => {
                const tarjetaActiva = document.querySelector('.selector-temas .opcion-tema.activo');
                const temaActual = tarjetaActiva ? tarjetaActiva.getAttribute('data-tema-opcion') : 'claro';
                const selectIdiomaActual = document.getElementById('idioma');
                const idiomaActual = selectIdiomaActual ? selectIdiomaActual.value : 'es';

                aplicarTemaGlobal(temaActual);
                aplicarIdiomaGlobal(idiomaActual);
            });
        }
    });
})();