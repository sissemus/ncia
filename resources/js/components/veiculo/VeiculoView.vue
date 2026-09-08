<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-ambulance</v-icon>
                <v-toolbar-title>Cadastro de Veículos</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Novo veículo" fab small elevation="2" color="primary" dark @click="novoVeiculo">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-text-field label="Identificação do Veículo" autocomplete="off" hide-details
                            v-model="veiculoPesquisa.VEICULO_IDENTIFICACAO"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select label="Tipo de Veículo" :items="tiposVeiculo" item-value="COLUNA_ID" item-text="DESCRICAO"
                            clearable hide-details v-model="veiculoPesquisa.TG_TIPO_VEICULO_ID"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select label="Situação para Uso" :items="situacoesVeiculo" item-value="COLUNA_ID" item-text="DESCRICAO"
                            clearable hide-details v-model="veiculoPesquisa.TG_SITUACAO_VEICULO_ID"></v-select>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-select label="Ativo" :items="ativos" item-value="id" item-text="text" clearable
                            hide-details v-model="veiculoPesquisa.VEICULO_ATIVO"></v-select>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">pesquisar</v-btn>
                        <v-btn color="red" dark tile @click="clear">limpar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-simple-table dense v-show="veiculos.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Identificação</th>
                            <th class="text-left">Placa</th>
                            <th class="text-left">Tipo</th>
                            <th class="text-left">Situação</th>
                            <th class="text-left">Unidade Vinculada</th>
                            <th class="text-left">Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="veiculo in veiculos" :key="veiculo.VEICULO_ID">
                            <td>{{ veiculo.VEICULO_ID }}</td>
                            <td>{{ veiculo.VEICULO_IDENTIFICACAO }}</td>
                            <td>{{ veiculo.VEICULO_PLACA || '-' }}</td>
                            <td>{{ veiculo.tipoVeiculo ? veiculo.tipoVeiculo.DESCRICAO : '-' }}</td>
                            <td>
                                <v-chip x-small :color="veiculo.TG_SITUACAO_VEICULO_ID === 1 ? 'green' : 'red'" dark>
                                    {{ veiculo.situacaoVeiculo ? veiculo.situacaoVeiculo.DESCRICAO : '-' }}
                                </v-chip>
                            </td>
                            <td>
                                {{ veiculo.vinculoAtivo && veiculo.vinculoAtivo.unidade ? veiculo.vinculoAtivo.unidade.UNIDADE_NOME : '-' }}
                            </td>
                            <td>
                                <v-chip x-small v-if="veiculo.VEICULO_ATIVO === 1" color="green" dark>Sim</v-chip>
                                <v-chip x-small v-else color="red" dark>Não</v-chip>
                            </td>
                            <td>
                                <v-btn icon @click="selecionar(veiculo)" title="Editar">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                                <v-btn v-show="veiculo.VEICULO_ATIVO === 1" icon @click="deletar(veiculo)"
                                    title="Inativar">
                                    <v-icon>mdi-delete</v-icon>
                                </v-btn>
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

        <MdNovoVeiculo :tipos-veiculo="tiposVeiculo" :situacoes-veiculo="situacoesVeiculo" :unidades="unidades" @salvo="search"></MdNovoVeiculo>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoVeiculo from "./MdNovoVeiculo";

export default {
    name: "VeiculoView",
    components: { MdNovoVeiculo, TratarErroAjax },
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
            msgId: 'msgVeiculoView',
            msgIdDebug: 'msgVeiculoViewDebug',
            ativos: [
                { id: 1, text: 'Sim' },
                { id: 0, text: 'Não' }
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
        veiculos: {
            get() { return this.$store.getters['VeiculoViewModule/getVeiculos'] },
            set(newValue) { this.$store.dispatch('VeiculoViewModule/setVeiculos', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['VeiculoViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('VeiculoViewModule/setPagination', newValue) }
        },
        veiculoPesquisa: {
            get() { return this.$store.getters['VeiculoViewModule/getVeiculoPesquisa'] },
            set(newValue) { this.$store.dispatch('VeiculoViewModule/setVeiculoPesquisa', newValue) }
        },
    },
    methods: {
        search() {
            this.$store.dispatch('VeiculoViewModule/search', this.msgId);
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.veiculoPesquisa = null;
            this.pagination.current_page = 1;
            this.search();
        },

        novoVeiculo() {
            this.$store.dispatch('MdNovoVeiculoModule/setVeiculo', null);
            this.$store.dispatch('MdNovoVeiculoModule/setShowModal', true);
        },

        selecionar(veiculo) {
            this.$store.dispatch('MdNovoVeiculoModule/setVeiculo', veiculo);
            this.$store.dispatch('MdNovoVeiculoModule/setShowModal', true);
        },

        deletar(veiculo) {
            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja inativar o veículo ${veiculo.VEICULO_IDENTIFICACAO}?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    let params = { id: veiculo.VEICULO_ID };

                    axios.delete(`${this.baseUrl}/veiculo/deletar`, { params })
                        .then(() => {
                            Swal.fire('Sucesso', 'Veículo inativado com sucesso', 'success');
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
        }
    }
}
</script>

<style></style>
