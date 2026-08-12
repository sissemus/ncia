<template>
    <div>
        <v-row justify="center">
            <v-dialog
                v-model="showModal"
                persistent
                width="800"
                scrollable
                :fullscreen="fullScreen"
            >
                <v-card>
                    <v-toolbar
                        color="primary"
                        elevation="1"
                        class="flex-grow-0"
                        dark
                    >
                        <v-toolbar-title>
                            Detalhes da Equipe
                        </v-toolbar-title>

                        <v-spacer></v-spacer>

                        <v-btn
                            icon
                            @click="fullScreen = true"
                            v-show="fullScreen === false"
                        >
                            <v-icon>
                                mdi-window-maximize
                            </v-icon>
                        </v-btn>

                        <v-btn
                            icon
                            @click="fullScreen = false"
                            v-show="fullScreen === true"
                        >
                            <v-icon>
                                mdi-window-restore
                            </v-icon>
                        </v-btn>

                        <v-btn
                            icon
                            @click="clearFormAndClose"
                        >
                            <v-icon>
                                mdi-close
                            </v-icon>
                        </v-btn>
                    </v-toolbar>

                    <tratar-erro-ajax
                        :id="msgId"
                    ></tratar-erro-ajax>

                    <div :id="msgIdDebug"></div>

                    <v-card-text class="mt-5">
                        <v-row>
                            <v-col cols="4">
                                <label>Data início*</label>

                                <input
                                    type="date"
                                    v-model="dataInicioFormatada"
                                >
                            </v-col>

                            <v-col cols="2">
                                <label>Hora início*</label>

                                <input
                                    type="time"
                                    v-model="horaInicioFormatada"
                                >
                            </v-col>

                            <v-col cols="4">
                                <label>Data final*</label>

                                <input
                                    type="date"
                                    v-model="dataFimFormatada"
                                >
                            </v-col>

                            <v-col cols="2">
                                <label>Hora final*</label>

                                <input
                                    type="time"
                                    v-model="horaFimFormatada"
                                >
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col>
                                <v-select
                                    label="Ativo*"
                                    :items="ativos"
                                    item-value="id"
                                    item-text="text"
                                    v-model="equipe.EQUIPE_ATIVO"
                                ></v-select>
                            </v-col>

                            <v-col>
                                <v-select
                                    label="Veículo*"
                                    :items="veiculos"
                                    item-value="VEICULO_ID"
                                    item-text="VEICULO_IDENTIFICACAO"
                                    v-model="equipe.VEICULO_ID"
                                ></v-select>
                            </v-col>

                            <v-col>
                                <v-select
                                    label="Profissional*"
                                    :items="profissionais"
                                    item-value="PROFISSIONAL_ID"
                                    item-text="PROFISSIONAL_NOME"
                                    v-model="equipe.PROFISSIONAL_ID"
                                ></v-select>
                            </v-col>
                        </v-row>
                    </v-card-text>

                    <v-divider class="ma-0"></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn
                            color="primary"
                            dark
                            tile
                            @click="salvar"
                        >
                            salvar
                        </v-btn>

                        <v-btn
                            color="red"
                            dark
                            outlined
                            tile
                            @click="clearFormAndClose"
                        >
                            fechar
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import TratarErroAjax from '../assets/TratarErroAjax';
import Swal from 'sweetalert2';

export default {
    name: 'MdNovoEquipe',

    components: {
        TratarErroAjax
    },

    data() {
        return {
            msgId: 'msgMdNovoEquipe',
            msgIdDebug: 'msgMdNovoEquipeDebug',

            ativos: [
                {
                    id: 1,
                    text: 'Sim'
                },
                {
                    id: 0,
                    text: 'Não'
                }
            ]
        };
    },

    mounted() {
        this.$store.dispatch(
            'VeiculoViewModule/search',
            this.msgId
        );

        this.$store.dispatch(
            'ProfissionalViewModule/search',
            this.msgId
        );
    },

    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),

        showModal: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getShowModal'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setShowModal',
                    valor
                );
            }
        },

        fullScreen: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getFullScreen'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setFullScreen',
                    valor
                );
            }
        },

        equipe: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getEquipe'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setEquipe',
                    valor
                );
            }
        },

        veiculos() {
            return this.$store.getters[
                'VeiculoViewModule/getVeiculos'
            ];
        },

        profissionais() {
            return this.$store.getters[
                'ProfissionalViewModule/getProfissionais'
            ];
        },

        dataInicioFormatada: {
            get() {
                return this.formatarDataParaInput(
                    this.equipe &&
                    this.equipe.EQUIPE_DATA_INI
                );
            },

            set(valor) {
                this.atualizarData(
                    'EQUIPE_DATA_INI',
                    valor
                );
            }
        },

        horaInicioFormatada: {
            get() {
                return this.formatarHoraParaInput(
                    this.equipe &&
                    this.equipe.EQUIPE_DATA_INI
                );
            },

            set(valor) {
                this.atualizarHora(
                    'EQUIPE_DATA_INI',
                    valor
                );
            }
        },

        dataFimFormatada: {
            get() {
                return this.formatarDataParaInput(
                    this.equipe &&
                    this.equipe.EQUIPE_DATA_FIM
                );
            },

            set(valor) {
                this.atualizarData(
                    'EQUIPE_DATA_FIM',
                    valor
                );
            }
        },

        horaFimFormatada: {
            get() {
                return this.formatarHoraParaInput(
                    this.equipe &&
                    this.equipe.EQUIPE_DATA_FIM
                );
            },

            set(valor) {
                this.atualizarHora(
                    'EQUIPE_DATA_FIM',
                    valor
                );
            }
        }
    },

    methods: {
        clearFormAndClose() {
            this.$store.dispatch(
                'TratarErroAjaxModule/fecharAlert',
                this.msgId
            );

            this.equipe = null;
            this.showModal = false;
        },

        salvar() {
            this.$store.dispatch(
                'TratarErroAjaxModule/fecharAlert',
                this.msgId
            );

            if (!this.equipe) {
                return;
            }

            const dados = {
                ...this.equipe,

                EQUIPE_DATA_INI: this.mesclarDataHora(
                    this.dataInicioFormatada,
                    this.horaInicioFormatada
                ),

                EQUIPE_DATA_FIM: this.mesclarDataHora(
                    this.dataFimFormatada,
                    this.horaFimFormatada
                )
            };

            const estaEditando =
                dados.EQUIPE_ID !== null &&
                dados.EQUIPE_ID !== undefined;

            axios({
                method: estaEditando
                    ? 'PUT'
                    : 'POST',

                url: estaEditando
                    ? `${this.baseUrl}/equipe/alterar`
                    : `${this.baseUrl}/equipe/inserir`,

                data: dados
            })
                .then(() => {
                    this.clearFormAndClose();

                    Swal.fire(
                        'Sucesso',
                        'Salvo com sucesso',
                        'success'
                    ).then(() => {
                        this.$store.dispatch(
                            'EquipeViewModule/search',
                            this.msgId
                        );
                    });
                })
                .catch(error => {
                    console.error(
                        'ERRO: ',
                        error
                    );

                    this.$store.dispatch(
                        'TratarErroAjaxModule/tratarErro',
                        {
                            id: this.msgId,
                            response: error.response
                        }
                    );
                });
        },

        formatarDataParaInput(valor) {
            if (!valor) {
                return '';
            }

            /*
             * Caso o Laravel retorne:
             *
             * 2026-08-12 00:00:00.000
             */
            if (
                /^\d{4}-\d{2}-\d{2} /.test(valor)
            ) {
                return valor.substring(0, 10);
            }

            /*
             * Caso venha no formato:
             *
             * 12-08-2026
             */
            if (
                /^\d{2}-\d{2}-\d{4}/.test(valor)
            ) {
                const partes = valor
                    .substring(0, 10)
                    .split('-');

                return [
                    partes[2],
                    partes[1],
                    partes[0]
                ].join('-');
            }

            /*
             * Caso o Laravel retorne ISO:
             *
             * 2026-08-12T03:00:00.000000Z
             */
            const iso = valor.replace(
                /\.(\d{3})\d+Z$/,
                '.$1Z'
            );

            const data = new Date(iso);

            if (isNaN(data.getTime())) {
                return '';
            }

            const partes = new Intl.DateTimeFormat(
                'en-CA',
                {
                    timeZone: 'America/Fortaleza',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                }
            ).formatToParts(data);

            const ano = partes.find(
                item => item.type === 'year'
            ).value;

            const mes = partes.find(
                item => item.type === 'month'
            ).value;

            const dia = partes.find(
                item => item.type === 'day'
            ).value;

            return `${ano}-${mes}-${dia}`;
        },

        formatarHoraParaInput(valor) {
            if (!valor) {
                return '';
            }

            /*
             * Caso o Laravel retorne:
             *
             * 2026-08-12 00:00:00.000
             */
            if (
                /^\d{4}-\d{2}-\d{2} /.test(valor)
            ) {
                return valor.substring(11, 16);
            }

            /*
             * Caso venha apenas como HH:mm
             */
            if (
                /^\d{2}:\d{2}/.test(valor)
            ) {
                return valor.substring(0, 5);
            }

            /*
             * Caso o Laravel retorne ISO:
             *
             * 2026-08-12T03:00:00.000000Z
             */
            const iso = valor.replace(
                /\.(\d{3})\d+Z$/,
                '.$1Z'
            );

            const data = new Date(iso);

            if (isNaN(data.getTime())) {
                return '';
            }

            return new Intl.DateTimeFormat(
                'pt-BR',
                {
                    timeZone: 'America/Fortaleza',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                }
            ).format(data);
        },

        atualizarData(campo, data) {
            if (!this.equipe || !data) {
                return;
            }

            const horaAtual =
                this.formatarHoraParaInput(
                    this.equipe[campo]
                ) || '00:00';

            /*
             * Atualiza diretamente o campo original.
             *
             * Não faça:
             *
             * this.dataInicioFormatada = valor;
             *
             * pois isso chama o setter novamente.
             */
            this.equipe[campo] =
                `${data} ${horaAtual}:00`;
        },

        atualizarHora(campo, hora) {
            if (!this.equipe || !hora) {
                return;
            }

            const dataAtual =
                this.formatarDataParaInput(
                    this.equipe[campo]
                );

            if (!dataAtual) {
                return;
            }

            /*
             * Atualiza diretamente o campo original.
             *
             * Resultado:
             * 2026-08-12 14:30:00
             */
            this.equipe[campo] =
                `${dataAtual} ${hora}:00`;
        },

        mesclarDataHora(data, hora) {
            if (!data || !hora) {
                return '';
            }

            /*
             * Resultado:
             * 2026-08-12 14:30:00
             */
            return `${data} ${hora}:00`;
        }
    }
};
</script>

<style scoped>
input[type='date'],
input[type='time'] {
    width: 100%;
    min-height: 40px;
    padding: 8px;
    border: 1px solid #bdbdbd;
    border-radius: 4px;
    font-size: 14px;
}

label {
    display: block;
    margin-bottom: 6px;
    color: #555;
    font-size: 14px;
}
</style>