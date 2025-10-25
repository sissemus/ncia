<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Departamentos</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Novo departamento" fab small elevation="2" color="primary" dark @click="novoDepartamento">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>
            <v-card-text>
                <v-row>
                    <v-col>
                        <v-text-field label="Nome" autocomplete="off" hide-details
                            v-model="departamentoPesquisa.DEPARTAMENTO_NOME"></v-text-field>
                    </v-col>
                    <v-col>
                        <v-text-field label="Sigla" autocomplete="off" hide-details
                            v-model="departamentoPesquisa.DEPARTAMENTO_SIGLA"></v-text-field>
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
            <v-simple-table dense v-show="departamentos.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Nome do Departamento</th>
                            <th class="text-left">Hierarquia Superior</th>
                            <th class="text-left">Sigla do Departamento</th>
                            <th class="text-left">Descrição</th>
                            <th class="text-left">Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="departamento in departamentos" :key="departamento['DEPARTAMENTO_ID']">
                            <td>{{ departamento['DEPARTAMENTO_ID'] }}</td>
                            <td>{{ departamento['DEPARTAMENTO_NOME'] }}</td>
                            <td>{{ departamento['DEPARTAMENTO_SIGLA'] }}</td>
                            <td>
                                {{
                                    departamento.hierarquia
                                        ? departamento.hierarquia["DESCRICAO"]
                                        : ""
                                }}
                            </td>
                            <td>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on, attrs }">
                                        <span v-bind="attrs" v-on="on">
                                            {{ truncateText(departamento['DEPARTAMENTO_DESCRICAO'], 30) }}
                                        </span>
                                    </template>
                                    <span>{{ departamento['DEPARTAMENTO_DESCRICAO'] }}</span>
                                </v-tooltip>
                            </td>
                            <td>
                                <v-chip x-small v-if="departamento['DEPARTAMENTO_ATIVO'] === 1" color="green" dark>Sim
                                </v-chip>
                                <v-chip x-small v-else color="red" dark>Não</v-chip>
                            </td>
                            <td>
                                <v-btn icon @click="selecionar(departamento)" title="Editar">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                                <v-btn icon @click="deletar(departamento)" title="Remover">
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
        <MdNovoDepartamento></MdNovoDepartamento>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoDepartamento from "./MdNovoDepartamento";

export default {
    name: "DepartamentoView",
    components: { MdNovoDepartamento, TratarErroAjax },
    props: {
        hierarquias: {
            type: Array,
        },
    },
    data() {
        return {
            msgId: 'msgDepartamentoView',
            msgIdDebug: 'msgDepartamentoViewDebug',
        }
    },
    mounted() {
        // Carga inicial
        this.search();
        this.$store.dispatch("DominioModule/setHierarquias", this.hierarquias);
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        departamentos: {
            get() { return this.$store.getters['DepartamentoViewModule/getDepartamentos'] },
            set(newValue) { this.$store.dispatch('DepartamentoViewModule/setDepartamentos', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['DepartamentoViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('DepartamentoViewModule/setPagination', newValue) }
        },
        departamentoPesquisa: {
            get() { return this.$store.getters['DepartamentoViewModule/getDepartamentoPesquisa'] },
            set(newValue) { this.$store.dispatch('DepartamentoViewModule/setDepartamentoPesquisa', newValue) }
        },
    },
    methods: {
        search() {
            this.$store.dispatch('DepartamentoViewModule/search', this.msgId);
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.departamentoPesquisa = {
                DEPARTAMENTO_NOME: null,
                DEPARTAMENTO_SIGLA: null
            };
            this.pagination.current_page = 1;
            this.search();
        },

        novoDepartamento() {
            this.$store.dispatch('MdNovoDepartamentoModule/setShowModal', true)
        },

        selecionar(departamento) {
            console.log(departamento);
            this.$store.dispatch('MdNovoDepartamentoModule/setDepartamento', departamento)
            this.$store.dispatch('MdNovoDepartamentoModule/setShowModal', true)
        },

        deletar(departamento) {
            let params = {
                id: departamento.DEPARTAMENTO_ID
            }

            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja excluir o departamento ${departamento.DEPARTAMENTO_NOME} ?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            })
                .then(result => {
                    if (result.isConfirmed)
                        axios.delete(`${this.baseUrl}/departamento/deletar`, { params })
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
