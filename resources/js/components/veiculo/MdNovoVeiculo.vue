<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                        <v-toolbar-title>Detalhes do Veículo</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="fullScreen = true" v-show="fullScreen === false">
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>
                        <v-btn icon @click="fullScreen = false" v-show="fullScreen === true">
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>
                        <v-btn icon @click="clearFormAndClose">
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
                                        v-model="veiculo.VEICULO_IDENTIFICACAO"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="Placa (Opcional)" autocomplete="off" outlined dense
                                        v-model="veiculo.VEICULO_PLACA"></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" md="4">
                                    <v-select label="Tipo de Veículo*" :items="tiposVeiculo" item-value="COLUNA_ID" outlined dense
                                        item-text="DESCRICAO" v-model="veiculo.TG_TIPO_VEICULO_ID"></v-select>
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-select label="Situação para Uso*" :items="situacoesVeiculo" item-value="COLUNA_ID" outlined dense
                                        item-text="DESCRICAO" v-model="veiculo.TG_SITUACAO_VEICULO_ID"></v-select>
                                </v-col>
                                <v-col cols="12" md="4">
                                    <v-select label="Ativo*" :items="ativos" item-value="id" item-text="text" outlined dense
                                        v-model="veiculo.VEICULO_ATIVO"></v-select>
                                </v-col>
                            </v-row>
                        </fieldset>

                        <fieldset class="custom-fieldset">
                            <legend class="custom-legend">VÍNCULO</legend>
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-autocomplete label="Unidade de Saúde" :items="unidades" item-value="UNIDADE_ID"
                                        item-text="UNIDADE_NOME" v-model="veiculo.UNIDADE_ID" clearable outlined dense
                                        :menu-props="{ offsetY: true }"></v-autocomplete>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field label="Data inicial do vínculo" type="date" autocomplete="off" outlined dense
                                        v-model="veiculo.VEICULO_UNIDADE_DT_INI"></v-text-field>
                                </v-col>
                            </v-row>
                        </fieldset>
                    </v-card-text>

                    <v-divider class="ma-0"></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark tile @click="salvar">salvar</v-btn>
                        <v-btn color="red" dark outlined tile @click="clearFormAndClose">fechar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import Swal from "sweetalert2";

export default {
    name: "MdNovoVeiculo",
    components: { TratarErroAjax },
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
            ativos: [
                { id: 1, text: 'Sim' },
                { id: 0, text: 'Não' }
            ]
        }
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
        veiculo: {
            get() { return this.$store.getters['MdNovoVeiculoModule/getVeiculo'] },
            set(newValue) { this.$store.dispatch('MdNovoVeiculoModule/setVeiculo', newValue) }
        }
    },
    methods: {
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);
            this.veiculo = null;
            this.fullScreen = false;
            this.showModal = false;
        },

        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);

            axios({
                method: this.veiculo.VEICULO_ID === null ? 'POST' : 'PUT',
                url: this.veiculo.VEICULO_ID === null
                    ? `${this.baseUrl}/veiculo/inserir`
                    : `${this.baseUrl}/veiculo/alterar`,
                data: this.veiculo
            }).then(() => {
                this.clearFormAndClose();
                Swal.fire('Sucesso', 'Salvo com sucesso', 'success').then(() => {
                    this.$emit('salvo');
                });
            }).catch(e => {
                console.error('ERRO: ', e);
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                });
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
