<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Profissionais</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn title="Novo Profissional" fab small elevation="2"
                    color="primary" dark @click="novoProfissional">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-text-field label="Nome" autocomplete="off" hide-details
                            v-model="profissionalPesquisa.PROFISSIONAL_NOME"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="3">
                        <v-text-field label="CPF" v-mask="'###.###.###-##'" autocomplete="off" hide-details
                            v-model="profissionalPesquisa.PROFISSIONAL_CPF"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="3">
                        <v-select label="Tipo de Profissional" :items="tipos_profissional" item-value="COLUNA_ID"
                            item-text="DESCRICAO" clearable hide-details
                            v-model="profissionalPesquisa.TG_TIPO_PROFISSIONAL_ID"></v-select>
                    </v-col>

                    <v-col cols="12" md="2">
                        <v-select label="Ativo" :items="ativos" item-value="id" item-text="text" clearable hide-details
                            v-model="profissionalPesquisa.PROFISSIONAL_ATIVO"></v-select>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">Pesquisar</v-btn>
                        <v-btn color="red" dark tile @click="clear">Limpar</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-simple-table dense v-show="profissionais.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Nome</th>
                            <th class="text-left">CPF</th>
                            <th class="text-left">Nascimento</th>
                            <th class="text-left">Sexo</th>
                            <th class="text-left">Tipo de Profissional</th>
                            <th class="text-left">Ativo</th>
                            <th class="text-left">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="profissional in profissionais" :key="profissional.PROFISSIONAL_ID">
                            <td>{{ profissional.PROFISSIONAL_ID }}</td>
                            <td>{{ profissional.PROFISSIONAL_NOME }}</td>
                            <td>{{ formatarCpf(profissional.PROFISSIONAL_CPF) }}</td>
                            <td>{{ formatarDataBR(profissional.PROFISSIONAL_NASCIMENTO) }}</td>
                            <td>{{ profissional.sexo ? profissional.sexo.DESCRICAO : '' }}</td>
                            <td>
                                {{ profissional.tipoProfissional ? profissional.tipoProfissional.DESCRICAO : '' }}
                            </td>
                            <td>
                                <v-chip x-small v-if="profissional.PROFISSIONAL_ATIVO === 1" color="green" dark>
                                    Sim
                                </v-chip>
                                <v-chip x-small v-else color="red" dark>
                                    Não
                                </v-chip>
                            </td>
                            <td>
                                <v-btn icon @click="selecionar(profissional)"
                                    title="Editar">
                                    <v-icon>mdi-pencil</v-icon>
                                </v-btn>

                                <v-btn v-show="profissional.PROFISSIONAL_ATIVO === 1"
                                    icon @click="deletar(profissional)" title="Inativar">
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
                            :length="pagination.last_page" total-visible="10"
                            @input="onPageChange"></v-pagination>
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

        <MdNovoProfissional :sexos="sexos" :tipos-profissional="tipos_profissional"
            @salvo="search"></MdNovoProfissional>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoProfissional from "./MdNovoProfissional";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "ProfissionalView",
    components: { MdNovoProfissional, TratarErroAjax },
    mixins: [UtilsMixins],
    props: {
        sexos: {
            type: Array,
            default: () => []
        },
        tipos_profissional: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            msgId: "msgProfissionalView",
            msgIdDebug: "msgProfissionalViewDebug",
            ativos: [
                { id: 1, text: "Sim" },
                { id: 0, text: "Não" }
            ]
        }
    },
    mounted() {
        this.search();
    },
    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl",
        }),
        profissionais: {
            get() {
                return this.$store.getters["ProfissionalViewModule/getProfissionais"]
            },
            set(newValue) {
                this.$store.dispatch("ProfissionalViewModule/setProfissionais", newValue)
            }
        },
        pagination: {
            get() {
                return this.$store.getters["ProfissionalViewModule/getPagination"]
            },
            set(newValue) {
                this.$store.dispatch("ProfissionalViewModule/setPagination", newValue)
            }
        },
        profissionalPesquisa: {
            get() {
                return this.$store.getters["ProfissionalViewModule/getProfissionalPesquisa"]
            },
            set(newValue) {
                this.$store.dispatch("ProfissionalViewModule/setProfissionalPesquisa", newValue)
            }
        }
    },
    methods: {
        search() {
            this.$store.dispatch("ProfissionalViewModule/search", this.msgId)
        },

        onPageChange() {
            this.search()
        },

        pesquisar() {
            this.pagination.current_page = 1
            this.search()
        },

        clear() {
            this.profissionalPesquisa = null
            this.pagination.current_page = 1
            this.search()
        },

        novoProfissional() {
            this.$store.dispatch("MdNovoProfissionalModule/setProfissional", null)
            this.$store.dispatch("MdNovoProfissionalModule/setShowModal", true)
        },

        selecionar(profissional) {
            this.$store.dispatch("MdNovoProfissionalModule/setProfissional", profissional)
            this.$store.dispatch("MdNovoProfissionalModule/setShowModal", true)
        },

        deletar(profissional) {
            Swal.fire({
                icon: "warning",
                title: "Alerta",
                text: `Deseja inativar o profissional ${profissional.PROFISSIONAL_NOME}?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: "Confirmar",
                denyButtonText: "Cancelar"
            }).then(result => {
                if (result.isConfirmed) {
                    let params = {
                        PROFISSIONAL_ID: profissional.PROFISSIONAL_ID
                    }

                    axios.delete(`${this.baseUrl}/profissional/deletar`, { params })
                        .then(() => {
                            Swal.fire("Sucesso", "Profissional inativado com sucesso", "success")
                            this.search()
                        })
                        .catch(e => {
                            console.error("ERRO: ", e)
                            this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                                id: this.msgId,
                                response: e.response
                            })
                        })
                }
            })
        },

        formatarCpf(cpf) {
            if (!cpf)
                return ""

            cpf = cpf.toString().replace(/\D/g, "")

            return cpf.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, "$1.$2.$3-$4")
        }
    }
}
</script>

<style></style>