<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullScreen">
                <v-card v-if="showModal" :key="formKey">
                    <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                        <v-toolbar-title>
                            {{ form.VEICULO_ID ? 'Editar Veículo / Mudar Vínculo' : 'Cadastro de Veículo' }}
                        </v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="fullScreen = true" v-show="fullScreen === false">
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>
                        <v-btn icon @click="fullScreen = false" v-show="fullScreen === true">
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>
                        <v-btn icon :disabled="processando" @click="clearFormAndClose">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>

                    <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                    <div :id="msgIdDebug"></div>

                    <v-card-text class="mt-5">
                        <fieldset class="custom-fieldset mb-5">
                            <legend class="custom-legend">DADOS DO VEÍCULO</legend>
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-text-field label="Identificação do Veículo*" autocomplete="off" outlined dense
                                        v-model="form.VEICULO_IDENTIFICACAO"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="Placa (Opcional)" autocomplete="off" outlined dense
                                        v-model="form.VEICULO_PLACA"></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" md="4">
                                    <v-select label="Tipo de Veículo*" :items="tiposVeiculo" item-value="COLUNA_ID" outlined dense
                                        item-text="DESCRICAO" v-model="form.TG_TIPO_VEICULO_ID"></v-select>
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-select label="Situação para Uso*" :items="situacoesVeiculo" item-value="COLUNA_ID" outlined dense
                                        item-text="DESCRICAO" v-model="form.TG_SITUACAO_VEICULO_ID"></v-select>
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-select label="Ativo*" :items="ativos" item-value="id" item-text="text" outlined dense
                                        v-model="form.VEICULO_ATIVO"></v-select>
                                </v-col>
                            </v-row>
                        </fieldset>

                        <fieldset class="custom-fieldset">
                            <legend class="custom-legend">VÍNCULO (OBRIGATÓRIO)</legend>
                            
                            <v-alert v-if="form.VEICULO_ID && unidadeOriginalId" dense text type="info" class="caption mb-3">
                                <span>Vínculo atual: <strong>{{ getNomeUnidadeOriginal() }}</strong> (desde {{ formatarDataBR(dataOriginalVinculo) }})</span>
                                <div class="mt-1" v-if="houveMudancaUnidade">
                                    <strong class="deep-orange--text text--darken-3">Atenção:</strong> Houve mudança na unidade vinculada. A data inicial do vínculo <strong>deve ser alterada obrigatoriamente</strong> para a nova data.
                                </div>
                            </v-alert>

                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-autocomplete
                                        :key="'unidade-' + formKey"
                                        label="Unidade de Saúde*"
                                        :items="unidadesSolicitantesAtivas"
                                        item-value="UNIDADE_ID"
                                        item-text="UNIDADE_NOME"
                                        v-model="form.UNIDADE_ID"
                                        @change="onUnidadeChange"
                                        clearable
                                        outlined
                                        dense
                                        :menu-props="{ offsetY: true }"
                                    ></v-autocomplete>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field
                                        label="Data inicial do vínculo*"
                                        type="date"
                                        autocomplete="off"
                                        outlined
                                        dense
                                        v-model="form.VEICULO_UNIDADE_DT_INI"
                                        :error-messages="mensagemErroData"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </fieldset>
                    </v-card-text>

                    <v-divider class="ma-0"></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark tile :loading="processando" @click="salvar">salvar</v-btn>
                        <v-btn color="red" dark outlined tile :disabled="processando" @click="clearFormAndClose">fechar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import UtilsMixins from "../../mixins/UtilsMixins";
import Swal from "sweetalert2";

export default {
    name: "MdNovoVeiculo",
    components: { TratarErroAjax },
    mixins: [UtilsMixins],
    props: {
        tiposVeiculo: {
            type: Array,
            default: () => []
        },
        situacoesVeiculo: {
            type: Array,
            default: () => []
        },
        unidades: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            msgId: 'msgMdNovoVeiculo',
            msgIdDebug: 'msgMdNovoVeiculoDebug',
            formKey: 0,
            processando: false,
            unidadeOriginalId: null,
            dataOriginalVinculo: null,
            ativos: [
                { id: 1, text: 'Sim' },
                { id: 0, text: 'Não' }
            ],
            form: {
                VEICULO_ID: null,
                VEICULO_IDENTIFICACAO: '',
                VEICULO_PLACA: '',
                TG_TIPO_VEICULO_ID: null,
                TG_SITUACAO_VEICULO_ID: null,
                VEICULO_ATIVO: 1,
                UNIDADE_ID: null,
                VEICULO_UNIDADE_DT_INI: ''
            }
        };
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        showModal: {
            get() { return this.$store.getters['MdNovoVeiculoModule/getShowModal'] },
            set(newValue) { this.$store.dispatch('MdNovoVeiculoModule/setShowModal', newValue) }
        },
        fullScreen: {
            get() { return this.$store.getters['MdNovoVeiculoModule/getFullScreen'] },
            set(newValue) { this.$store.dispatch('MdNovoVeiculoModule/setFullScreen', newValue) }
        },
        veiculoStore() {
            return this.$store.getters['MdNovoVeiculoModule/getVeiculo'];
        },
        unidadesSolicitantesAtivas() {
            if (!this.unidades) return [];
            return this.unidades.filter(u => Number(u.UNIDADE_ATIVO) === 1 && Number(u.UNIDADE_SOLICITANTE) === 1);
        },
        houveMudancaUnidade() {
            return !!(
                this.form.VEICULO_ID &&
                this.unidadeOriginalId &&
                this.form.UNIDADE_ID &&
                Number(this.form.UNIDADE_ID) !== Number(this.unidadeOriginalId)
            );
        },
        mensagemErroData() {
            if (this.houveMudancaUnidade) {
                if (!this.form.VEICULO_UNIDADE_DT_INI) {
                    return 'A data inicial é obrigatória.';
                }
                if (this.form.VEICULO_UNIDADE_DT_INI === this.dataOriginalVinculo) {
                    return 'A data do vínculo deve ser alterada quando a unidade for modificada.';
                }
                if (this.dataOriginalVinculo && this.form.VEICULO_UNIDADE_DT_INI < this.dataOriginalVinculo) {
                    return `A data não pode ser anterior ao vínculo anterior (${this.formatarDataBR(this.dataOriginalVinculo)}).`;
                }
            }
            return '';
        }
    },
    watch: {
        showModal(val) {
            if (val) {
                this.formKey++;
                this.carregarDados();
            }
        }
    },
    methods: {
        carregarDados() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);
            const veiculo = this.veiculoStore;
            const today = moment().format('YYYY-MM-DD');

            if (veiculo && veiculo.VEICULO_ID) {
                // Modo Edição / Mudança de Vínculo
                const vinculo = veiculo.vinculoAtivo || veiculo.vinculo_ativo;
                const unidadeId = vinculo && vinculo.UNIDADE_ID ? Number(vinculo.UNIDADE_ID) : null;
                const dtIni = vinculo && vinculo.VEICULO_UNIDADE_DT_INI
                    ? moment(vinculo.VEICULO_UNIDADE_DT_INI).format('YYYY-MM-DD')
                    : today;

                this.unidadeOriginalId = unidadeId;
                this.dataOriginalVinculo = dtIni;

                this.form = {
                    VEICULO_ID: veiculo.VEICULO_ID,
                    VEICULO_IDENTIFICACAO: veiculo.VEICULO_IDENTIFICACAO || '',
                    VEICULO_PLACA: veiculo.VEICULO_PLACA || '',
                    TG_TIPO_VEICULO_ID: veiculo.TG_TIPO_VEICULO_ID || null,
                    TG_SITUACAO_VEICULO_ID: veiculo.TG_SITUACAO_VEICULO_ID || null,
                    VEICULO_ATIVO: veiculo.VEICULO_ATIVO !== undefined ? Number(veiculo.VEICULO_ATIVO) : 1,
                    UNIDADE_ID: unidadeId,
                    VEICULO_UNIDADE_DT_INI: dtIni
                };
            } else {
                // Modo Novo Veículo (Cadastro) - Campo UNIDADE_ID SEMPRE LIMPO!
                this.unidadeOriginalId = null;
                this.dataOriginalVinculo = null;

                this.form = {
                    VEICULO_ID: null,
                    VEICULO_IDENTIFICACAO: '',
                    VEICULO_PLACA: '',
                    TG_TIPO_VEICULO_ID: null,
                    TG_SITUACAO_VEICULO_ID: null,
                    VEICULO_ATIVO: 1,
                    UNIDADE_ID: null,
                    VEICULO_UNIDADE_DT_INI: today
                };
            }
        },

        getNomeUnidadeOriginal() {
            if (!this.unidadeOriginalId || !this.unidades) return '-';
            const u = this.unidades.find(item => Number(item.UNIDADE_ID) === Number(this.unidadeOriginalId));
            return u ? u.UNIDADE_NOME : '-';
        },

        onUnidadeChange(novaUnidade) {
            if (this.form.VEICULO_ID && this.unidadeOriginalId) {
                if (novaUnidade && Number(novaUnidade) !== Number(this.unidadeOriginalId)) {
                    const today = moment().format('YYYY-MM-DD');
                    if (this.dataOriginalVinculo === today) {
                        this.form.VEICULO_UNIDADE_DT_INI = '';
                    } else if (today > this.dataOriginalVinculo) {
                        this.form.VEICULO_UNIDADE_DT_INI = today;
                    } else {
                        this.form.VEICULO_UNIDADE_DT_INI = '';
                    }
                } else if (Number(novaUnidade) === Number(this.unidadeOriginalId)) {
                    this.form.VEICULO_UNIDADE_DT_INI = this.dataOriginalVinculo;
                }
            }
        },

        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);
            this.$store.dispatch('MdNovoVeiculoModule/setVeiculo', null);
            this.form = {
                VEICULO_ID: null,
                VEICULO_IDENTIFICACAO: '',
                VEICULO_PLACA: '',
                TG_TIPO_VEICULO_ID: null,
                TG_SITUACAO_VEICULO_ID: null,
                VEICULO_ATIVO: 1,
                UNIDADE_ID: null,
                VEICULO_UNIDADE_DT_INI: ''
            };
            this.unidadeOriginalId = null;
            this.dataOriginalVinculo = null;
            this.fullScreen = false;
            this.showModal = false;
        },

        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);

            // 1. Validações dos campos do veículo
            if (!this.form.VEICULO_IDENTIFICACAO || !String(this.form.VEICULO_IDENTIFICACAO).trim()) {
                Swal.fire('Atenção', 'Informe a Identificação do Veículo.', 'warning');
                return;
            }
            if (!this.form.TG_TIPO_VEICULO_ID) {
                Swal.fire('Atenção', 'Selecione o Tipo de Veículo.', 'warning');
                return;
            }
            if (!this.form.TG_SITUACAO_VEICULO_ID) {
                Swal.fire('Atenção', 'Selecione a Situação para Uso.', 'warning');
                return;
            }
            if (this.form.VEICULO_ATIVO === null || this.form.VEICULO_ATIVO === undefined) {
                Swal.fire('Atenção', 'Informe se o Veículo está Ativo.', 'warning');
                return;
            }

            // 2. Validações OBRIGATÓRIAS dos campos do vínculo
            if (!this.form.UNIDADE_ID) {
                Swal.fire('Atenção', 'A Unidade de Saúde do vínculo é de preenchimento obrigatório.', 'warning');
                return;
            }
            if (!this.form.VEICULO_UNIDADE_DT_INI) {
                Swal.fire('Atenção', 'A Data inicial do vínculo é de preenchimento obrigatório.', 'warning');
                return;
            }

            // 3. Regra de mudança de unidade vinculada
            if (this.form.VEICULO_ID && this.unidadeOriginalId) {
                const mudouUnidade = Number(this.form.UNIDADE_ID) !== Number(this.unidadeOriginalId);
                if (mudouUnidade) {
                    if (this.form.VEICULO_UNIDADE_DT_INI === this.dataOriginalVinculo) {
                        Swal.fire(
                            'Atenção',
                            'Houve mudança na unidade vinculada ao veículo. A data inicial do vínculo deve ser alterada obrigatoriamente.',
                            'warning'
                        );
                        return;
                    }
                    if (this.dataOriginalVinculo && this.form.VEICULO_UNIDADE_DT_INI < this.dataOriginalVinculo) {
                        Swal.fire(
                            'Atenção',
                            `A data do novo vínculo não pode ser anterior à data do vínculo anterior (${this.formatarDataBR(this.dataOriginalVinculo)}).`,
                            'warning'
                        );
                        return;
                    }
                }
            }

            // 4. Salvar via API
            this.processando = true;
            const isNovo = this.form.VEICULO_ID === null;

            axios({
                method: isNovo ? 'POST' : 'PUT',
                url: isNovo ? `${this.baseUrl}/veiculo/inserir` : `${this.baseUrl}/veiculo/alterar`,
                data: this.form
            }).then(() => {
                this.clearFormAndClose();
                Swal.fire('Sucesso', 'Veículo salvo com sucesso!', 'success').then(() => {
                    this.$emit('salvo');
                });
            }).catch(e => {
                console.error('ERRO AO SALVAR VEÍCULO: ', e);
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                });
            }).finally(() => {
                this.processando = false;
            });
        }
    }
}
</script>

<style scoped>
.custom-fieldset {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 15px 20px 5px 20px;
}
.custom-legend {
    width: auto;
    padding: 0 10px;
    font-size: 14px;
    font-weight: bold;
    font-style: italic;
    color: #555;
}
</style>
