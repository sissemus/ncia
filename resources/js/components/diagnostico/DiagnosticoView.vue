<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Diagnósticos</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Novo diagnóstico" fab small elevation="2" color="primary" dark @click="novoDiagnostico">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>
            <v-card-text>
                <v-row>
                    <v-col>
                        <v-text-field label="Descrição" autocomplete="off" hide-details
                            v-model="diagnosticoPesquisa.DIAGNOSTICO_DESCRICAO"></v-text-field>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">pesquisar</v-btn>
                        <v-btn color="red" dark tile @click="clear">limpar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
            <v-simple-table dense v-show="diagnosticos.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Descrição do Diagnostico</th>
                            <th class="text-left">Ativo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="diagnostico in diagnosticos" :key="diagnostico['DIAGNOSTICO_ID']">
                            <td>{{ diagnostico['DIAGNOSTICO_ID'] }}</td>
                            <td>{{ diagnostico['DIAGNOSTICO_DESCRICAO'] }}</td>
                            <td>
                                <v-chip x-small v-if="diagnostico['DIAGNOSTICO_ATIVO'] === 1" color="green" dark>Sim
                                </v-chip>
                                <v-chip x-small v-else color="red" dark>Não</v-chip>
                            </td>
                            <td>
                                <v-btn icon @click="selecionar(diagnostico)" title="Editar">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>
                                <v-btn icon @click="deletar(diagnostico)" title="Remover">
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
        <MdNovoDiagnostico></MdNovoDiagnostico>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoDiagnostico from "./MdNovoDiagnostico";

export default {
    name: "DiagnosticoView",
    components: { MdNovoDiagnostico, TratarErroAjax },
    data() {
        return {
            msgId: 'msgDiagnosticoView',
            msgIdDebug: 'msgDiagnosticoViewDebug',
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
        diagnosticos: {
            get() { return this.$store.getters['DiagnosticoViewModule/getDiagnosticos'] },
            set(newValue) { this.$store.dispatch('DiagnosticoViewModule/setDiagnosticos', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['DiagnosticoViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('DiagnosticoViewModule/setPagination', newValue) }
        },
        diagnosticoPesquisa: {
            get() { return this.$store.getters['DiagnosticoViewModule/getDiagnosticoPesquisa'] },
            set(newValue) { this.$store.dispatch('DiagnosticoViewModule/setDiagnosticoPesquisa', newValue) }
        },
    },
    methods: {
        search() {
            this.$store.dispatch('DiagnosticoViewModule/search', this.msgId);
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.diagnosticoPesquisa = {
                DIAGNOSTICO_DESCRICAO: null
            };
            this.pagination.current_page = 1;
            this.search();
        },

        novoDiagnostico() {
            this.$store.dispatch('MdNovoDiagnosticoModule/setShowModal', true)
        },

        selecionar(diagnostico) {
            console.log(diagnostico);
            this.$store.dispatch('MdNovoDiagnosticoModule/setDiagnostico', diagnostico)
            this.$store.dispatch('MdNovoDiagnosticoModule/setShowModal', true)
        },

        deletar(diagnostico) {
            let params = {
                id: diagnostico.DIAGNOSTICO_ID
            }

            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja excluir o diagnostico ${diagnostico.DIAGNOSTICO_DESCRICAO} ?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            })
                .then(result => {
                    if (result.isConfirmed)
                        axios.delete(`${this.baseUrl}/diagnostico/deletar`, { params })
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
