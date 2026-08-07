<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar color="primary" elevation="1" class="flex-grow-0" dark>
                        <v-toolbar-title>{{ isEdit ? 'Alterar Unidade Vinculada' : 'Vincular Veículo a Unidade' }}</v-toolbar-title>
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
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-select label="Veículo*" :items="veiculos" item-value="VEICULO_ID"
                                    item-text="VEICULO_IDENTIFICACAO" v-model="vinculo.VEICULO_ID"
                                    :disabled="isEdit"></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select label="Unidade de Saúde*" :items="unidades" item-value="UNIDADE_ID"
                                    item-text="UNIDADE_NOME" v-model="vinculo.UNIDADE_ID"></v-select>
                            </v-col>
                        </v-row>
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
    name: "MdNovoVeiculoUnidade",
    components: { TratarErroAjax },
    props: {
        veiculos: {
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
            msgId: 'msgMdNovoVeiculoUnidade',
            msgIdDebug: 'msgMdNovoVeiculoUnidadeDebug'
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        showModal: {
            get() { return this.$store.getters['MdNovoVeiculoUnidadeModule/getShowModal'] },
            set(newValue) { this.$store.dispatch('MdNovoVeiculoUnidadeModule/setShowModal', newValue) }
        },
        fullScreen: {
            get() { return this.$store.getters['MdNovoVeiculoUnidadeModule/getFullScreen'] },
            set(newValue) { this.$store.dispatch('MdNovoVeiculoUnidadeModule/setFullScreen', newValue) }
        },
        vinculo: {
            get() { return this.$store.getters['MdNovoVeiculoUnidadeModule/getVinculo'] },
            set(newValue) { this.$store.dispatch('MdNovoVeiculoUnidadeModule/setVinculo', newValue) }
        },
        isEdit() {
            return this.vinculo && this.vinculo.VEICULO_UNIDADE_ID !== null;
        }
    },
    methods: {
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);
            this.vinculo = null;
            this.fullScreen = false;
            this.showModal = false;
        },

        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);

            axios({
                method: this.isEdit ? 'PUT' : 'POST',
                url: this.isEdit
                    ? `${this.baseUrl}/veiculo_unidade/alterar`
                    : `${this.baseUrl}/veiculo_unidade/inserir`,
                data: this.vinculo
            }).then(() => {
                this.clearFormAndClose();
                Swal.fire('Sucesso', 'Vínculo salvo com sucesso', 'success').then(() => {
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

<style scoped></style>
