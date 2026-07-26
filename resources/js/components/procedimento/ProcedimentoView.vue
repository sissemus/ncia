<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Procedimentos</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Novo procedimento" fab small elevation="2" color="primary" dark @click="novoProcedimento">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>
            <v-card-text>
                <v-row>
                    <v-col>
                        <v-text-field label="Descrição" autocomplete="off" hide-details
                            v-model="procedimentoPesquisa.PROCEDIMENTO_DESCRICAO"></v-text-field>
                    </v-col>
                    <v-col>
                        <v-text-field label="Código" autocomplete="off" hide-details
                            v-model="procedimentoPesquisa.PROCEDIMENTO_CODIGO"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>

                </v-row>
                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">pesquisar</v-btn>
                        <v-btn color="red" dark tile @click="clear">limpar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
            <v-simple-table dense v-show="procedimentos.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Descrição do Procedimento</th>
                            <th class="text-left">Código do Procedimento</th>
                            <th class="text-left">Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="procedimento in procedimentos" :key="procedimento['PROCEDIMENTO_ID']">
                            <td>{{ procedimento['PROCEDIMENTO_ID'] }}</td>
                            <td>{{ procedimento['PROCEDIMENTO_DESCRICAO'] }}</td>
                            <td>{{ procedimento['PROCEDIMENTO_CODIGO'] }}</td>
                            <td>
                                <v-chip x-small v-if="procedimento['PROCEDIMENTO_ATIVO'] === 1" color="green" dark>Sim
                                </v-chip>
                                <v-chip x-small v-else color="red" dark>Não</v-chip>
                            </td>
                            <td>
                                <v-btn icon @click="selecionar(procedimento)" title="Editar">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                                <v-btn icon @click="deletar(procedimento)" title="Remover">
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
                            {{ pagination.total }} registro{{ pagination.total > 1 ? 's' : '' }}
                        </v-chip>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>
        <MdNovoProcedimento></MdNovoProcedimento>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoProcedimento from "./MdNovoProcedimento";

export default {
    name: "ProcedimentoView",
    components: { MdNovoProcedimento, TratarErroAjax },
    props: {
        hierarquias: {
            type: Array,
        },
    },
    data() {
        return {
            msgId: 'msgProcedimentoView',
            msgIdDebug: 'msgProcedimentoViewDebug',
        }
    },
    mounted() {
        // Carga inicial
        this.search();
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        procedimentos: {
            get() { return this.$store.getters['ProcedimentoViewModule/getProcedimentos'] },
            set(newValue) { this.$store.dispatch('ProcedimentoViewModule/setProcedimentos', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['ProcedimentoViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('ProcedimentoViewModule/setPagination', newValue) }
        },
        procedimentoPesquisa: {
            get() { return this.$store.getters['ProcedimentoViewModule/getProcedimentoPesquisa'] },
            set(newValue) { this.$store.dispatch('ProcedimentoViewModule/setProcedimentoPesquisa', newValue) }
        },
    },
    methods: {
        search() {
            this.$store.dispatch('ProcedimentoViewModule/search', this.msgId);
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.procedimentoPesquisa = {
                PROCEDIMENTO_DESCRICAO: null,
                PROCEDIMENTO_CODIGO: null
            };
            this.pagination.current_page = 1;
            this.search();
        },

        novoProcedimento() {
            this.$store.dispatch('MdNovoProcedimentoModule/setShowModal', true)
        },

        selecionar(procedimento) {
            console.log(procedimento);
            this.$store.dispatch('MdNovoProcedimentoModule/setProcedimento', procedimento)
            this.$store.dispatch('MdNovoProcedimentoModule/setShowModal', true)
        },

        deletar(procedimento) {
            let params = {
                id: procedimento.PROCEDIMENTO_ID
            }

            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja excluir o procedimento ${procedimento.PROCEDIMENTO_DESCRICAO} ?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            })
                .then(result => {
                    if (result.isConfirmed)
                        axios.delete(`${this.baseUrl}/procedimento/deletar`, { params })
                            .then(res => {
                                Swal.fire('Excluído com sucesso!', '', 'success')
                                    .then(res => {
                                        this.search();
                                    })
                            })
                })
        },

        truncateText(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        }
    }
}
</script>

<style></style>
