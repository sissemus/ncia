<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-link</v-icon>
                <v-toolbar-title>Vínculo Veículo Unidade</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Vincular veículo" fab small elevation="2" color="primary" dark @click="novoVinculo">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-select label="Veículo" :items="veiculos" item-value="VEICULO_ID" item-text="VEICULO_IDENTIFICACAO"
                            clearable hide-details v-model="vinculoPesquisa.VEICULO_ID"></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-select label="Unidade de Saúde" :items="unidades" item-value="UNIDADE_ID" item-text="UNIDADE_NOME"
                            clearable hide-details v-model="vinculoPesquisa.UNIDADE_ID"></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-select label="Status do Vínculo" :items="statusOptions" item-value="id" item-text="text"
                            hide-details v-model="vinculoPesquisa.STATUS"></v-select>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">pesquisar</v-btn>
                        <v-btn color="red" dark tile @click="clear">limpar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-simple-table dense v-show="vinculos.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Veículo</th>
                            <th class="text-left">Unidade de Saúde</th>
                            <th class="text-left">Data Início</th>
                            <th class="text-left">Data Fim</th>
                            <th class="text-left">Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="vinculo in vinculos" :key="vinculo.VEICULO_UNIDADE_ID">
                            <td>{{ vinculo.VEICULO_UNIDADE_ID }}</td>
                            <td>{{ vinculo.veiculo ? vinculo.veiculo.VEICULO_IDENTIFICACAO : '-' }}</td>
                            <td>{{ vinculo.unidade ? vinculo.unidade.UNIDADE_NOME : '-' }}</td>
                            <td>{{ formatarDataHora(vinculo.VEICULO_UNIDADE_DT_INI) }}</td>
                            <td>{{ formatarDataHora(vinculo.VEICULO_UNIDADE_DT_FIM) }}</td>
                            <td>
                                <v-chip x-small :color="!vinculo.VEICULO_UNIDADE_DT_FIM ? 'green' : 'grey'" dark>
                                    {{ !vinculo.VEICULO_UNIDADE_DT_FIM ? 'Ativo' : 'Histórico' }}
                                </v-chip>
                            </td>
                            <td>
                                <template v-if="!vinculo.VEICULO_UNIDADE_DT_FIM">
                                    <v-btn icon @click="selecionar(vinculo)" title="Alterar Unidade Vinculada">
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>
                                    <v-btn icon @click="desvincular(vinculo)" title="Desvincular Veículo">
                                        <v-icon>mdi-link-off</v-icon>
                                    </v-btn>
                                </template>
                                <span v-else>-</span>
                            </td>
                        </tr>
                    </tbody>
                </template>
            </v-simple-table>

            <v-divider></v-divider>

            <v-card-actions>
                <v-row>
                    <v-col>
                        <v-pagination v-show="pagination.total" v-model="pagination.current_page"
                            :length="pagination.last_page" total-visible="10" @input="onPageChange"></v-pagination>
                    </v-col>
                </v-row>
            </v-card-actions>

            <v-divider></v-divider>

            <v-card-actions class="text-center">
                <v-row>
                    <v-col>
                        <v-chip>
                            {{ pagination.total }} registro{{ pagination.total !== 1 ? 's' : '' }}
                        </v-chip>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>

        <MdNovoVeiculoUnidade :veiculos="veiculos" :unidades="unidades" @salvo="search"></MdNovoVeiculoUnidade>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import moment from "moment";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoVeiculoUnidade from "./MdNovoVeiculoUnidade";

export default {
    name: "VeiculoUnidadeView",
    components: { MdNovoVeiculoUnidade, TratarErroAjax },
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
            msgId: 'msgVeiculoUnidadeView',
            msgIdDebug: 'msgVeiculoUnidadeViewDebug',
            statusOptions: [
                { id: 'todos', text: 'Todos' },
                { id: 'ativo', text: 'Ativos' },
                { id: 'historico', text: 'Históricos' }
            ]
        }
    },
    mounted() {
        this.search();
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        vinculos: {
            get() { return this.$store.getters['VeiculoUnidadeViewModule/getVinculos'] },
            set(newValue) { this.$store.dispatch('VeiculoUnidadeViewModule/setVinculos', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['VeiculoUnidadeViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('VeiculoUnidadeViewModule/setPagination', newValue) }
        },
        vinculoPesquisa: {
            get() { return this.$store.getters['VeiculoUnidadeViewModule/getVinculoPesquisa'] },
            set(newValue) { this.$store.dispatch('VeiculoUnidadeViewModule/setVinculoPesquisa', newValue) }
        },
    },
    methods: {
        search() {
            this.$store.dispatch('VeiculoUnidadeViewModule/search', this.msgId);
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.vinculoPesquisa = null;
            this.pagination.current_page = 1;
            this.search();
        },

        novoVinculo() {
            this.$store.dispatch('MdNovoVeiculoUnidadeModule/setVinculo', null);
            this.$store.dispatch('MdNovoVeiculoUnidadeModule/setShowModal', true);
        },

        selecionar(vinculo) {
            this.$store.dispatch('MdNovoVeiculoUnidadeModule/setVinculo', vinculo);
            this.$store.dispatch('MdNovoVeiculoUnidadeModule/setShowModal', true);
        },

        desvincular(vinculo) {
            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja desvincular o veículo ${vinculo.veiculo ? vinculo.veiculo.VEICULO_IDENTIFICACAO : ''} da unidade ${vinculo.unidade ? vinculo.unidade.UNIDADE_NOME : ''}?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    axios.post(`${this.baseUrl}/veiculo_unidade/desvincular`, { VEICULO_ID: vinculo.VEICULO_ID })
                        .then(() => {
                            Swal.fire('Sucesso', 'Veículo desvinculado com sucesso', 'success');
                            this.search();
                        })
                        .catch(e => {
                            console.error('ERRO: ', e);
                            this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                                id: this.msgId,
                                response: e.response
                            });
                        });
                }
            });
        },

        formatarDataHora(val) {
            if (!val) return '-';
            return moment(val).format('DD/MM/YYYY HH:mm:ss');
        }
    }
}
</script>

<style></style>
