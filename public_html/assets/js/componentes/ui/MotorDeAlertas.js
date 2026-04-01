/**
 * MotorDeAlertas.js
 * PAPEL: Centralizar e padronizar diálogos SweetAlert2 no Social BR.
 * RESPONSABILIDADE: Interface de usuário para avisos, erros e confirmações.
 * VERSÃO: 1.0 - Identidade Visual #0C2D54 - socialbr.lol
 */

const MotorDeAlertas = {

    // Configurações base de cores oficiais
    config: {
        corPrimaria: '#0C2D54',
        corSucesso: '#2ecc71',
        corErro: '#e74c3c',
        corAviso: '#f1c40f'
    },

    /**
     * Alerta de Sucesso
     * @param {string} titulo 
     * @param {string} mensagem 
     */
    sucesso(titulo, mensagem = '') {
        return Swal.fire({
            title: titulo,
            text: mensagem,
            icon: 'success',
            confirmButtonColor: this.config.corPrimaria,
            confirmButtonText: 'Ótimo',
            heightAuto: false
        });
    },

    /**
     * Alerta de Erro
     * @param {string} titulo 
     * @param {string} mensagem 
     */
    erro(titulo, mensagem = '') {
        return Swal.fire({
            title: titulo,
            text: mensagem,
            icon: 'error',
            confirmButtonColor: this.config.corPrimaria,
            confirmButtonText: 'Entendido',
            heightAuto: false
        });
    },

    /**
     * Janela de Confirmação (Retorna Promise)
     * Uso: MotorDeAlertas.confirmar('Título', 'Texto').then(confirmado => { ... })
     */
    async confirmar(titulo, mensagem, textoBotao = 'Sim, continuar', corBotao = '') {
        const resultado = await Swal.fire({
            title: titulo,
            text: mensagem,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: corBotao || this.config.corPrimaria,
            cancelButtonColor: '#d33',
            confirmButtonText: textoBotao,
            cancelButtonText: 'Cancelar',
            heightAuto: false,
            reverseButtons: true // UX: Botão de ação principal à direita
        });

        return resultado.isConfirmed;
    },

    /**
     * Alerta de Aviso/Alerta
     */
    aviso(titulo, mensagem = '') {
        return Swal.fire({
            title: titulo,
            text: mensagem,
            icon: 'warning',
            confirmButtonColor: this.config.corPrimaria,
            confirmButtonText: 'Ok',
            heightAuto: false
        });
    },

    /**
     * DICA DE OURO: Alerta com Input de Texto (Para Denúncias/Motivos)
     */
    async pedirTexto(titulo, placeholder, botaoTexto = 'Enviar') {
        const { value: text } = await Swal.fire({
            title: titulo,
            input: 'textarea',
            inputPlaceholder: placeholder,
            inputAttributes: {
                'aria-label': placeholder
            },
            showCancelButton: true,
            confirmButtonColor: this.config.corPrimaria,
            confirmButtonText: botaoTexto,
            cancelButtonText: 'Cancelar',
            heightAuto: false,
            inputValidator: (value) => {
                if (!value) {
                    return 'Você precisa escrever algo!';
                }
            }
        });

        return text;
    }
};

// Congelar o objeto para evitar modificações acidentais em runtime
Object.freeze(MotorDeAlertas);