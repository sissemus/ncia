<template>
    <div>
        <v-card>
            <v-toolbar class="elevation-1">
                <v-icon class="mr-1">mdi-database</v-icon>
                <v-toolbar-title>Cadastro de Pacientes</v-toolbar-title>
                <v-spacer></v-spacer>

                <v-btn title="Novo paciente" fab small elevation="2" color="primary" dark @click="novoPaciente">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text>
                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field label="Nome do Paciente" autocomplete="off" hide-details
                            v-model="pacientePesquisa.PACIENTE_NOME"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-text-field label="CPF" v-mask="'###.###.###-##'" autocomplete="off" hide-details
                            v-model="pacientePesquisa.PACIENTE_CPF"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="2">
                        <v-select label="Sexo" :items="sexosDominio" item-value="COLUNA_ID" item-text="DESCRICAO"
                            clearable hide-details v-model="pacientePesquisa.TG_SEXO_ID"></v-select>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col class="text-right">
                        <v-btn color="primary" tile @click="pesquisar">
                            pesquisar
                        </v-btn>

                        <v-btn color="red" dark tile @click="clear">
                            limpar
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-simple-table dense v-show="pacientes.length" class="mb-0">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Paciente</th>
                            <th class="text-left">CPF</th>
                            <th class="text-left">Nascimento</th>
                            <th class="text-left">Idade</th>
                            <th class="text-left">Sexo</th>
                            <th class="text-left">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="paciente in pacientes" :key="paciente.PACIENTE_ID">
                            <td>{{ paciente.PACIENTE_ID }}</td>
                            <td>{{ paciente.PACIENTE_NOME }}</td>
                            <td>{{ mascararCpf(paciente.PACIENTE_CPF) }}</td>
                            <td>{{ formatarDataBR(paciente.PACIENTE_DT_NASCIMENTO) }}</td>
                            <td>{{ calcularIdadeCompleta(paciente.PACIENTE_DT_NASCIMENTO) }}</td>
                            <td>{{ descricaoTabelaGenerica(sexosDominio, paciente.TG_SEXO_ID) }}</td>

                            <td>
                                <v-btn icon @click="selecionar(paciente)" title="Editar">
                                    <v-icon>mdi-pencil</v-icon>
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
                            {{ pagination.total }} registro{{ pagination.total !== 1 ? "s" : "" }}
                        </v-chip>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>

        <MdNovoPaciente @salvo="search"></MdNovoPaciente>
    </div>
</template>

<script>
import moment from "moment";
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoPaciente from "./MdNovoPaciente";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "PacienteView",
    components: { MdNovoPaciente, TratarErroAjax },
    mixins: [UtilsMixins],

    props: {
        sexos: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            msgId: "msgPacienteView",
            msgIdDebug: "msgPacienteViewDebug"
        }
    },

    mounted() {
        this.$store.dispatch("DominioModule/setSexos", this.sexos);
        this.search();
    },

    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        }),

        pacientes: {
            get() {
                return this.$store.getters["PacienteViewModule/getPacientes"];
            },
            set(newValue) {
                this.$store.dispatch("PacienteViewModule/setPacientes", newValue);
            }
        },

        pagination: {
            get() {
                return this.$store.getters["PacienteViewModule/getPagination"];
            },
            set(newValue) {
                this.$store.dispatch("PacienteViewModule/setPagination", newValue);
            }
        },

        pacientePesquisa: {
            get() {
                return this.$store.getters["PacienteViewModule/getPacientePesquisa"];
            },
            set(newValue) {
                this.$store.dispatch("PacienteViewModule/setPacientePesquisa", newValue);
            }
        },

        sexosDominio() {
            return this.$store.getters["DominioModule/getSexos"];
        }
    },

    methods: {
        search() {
            this.$store.dispatch("PacienteViewModule/search", this.msgId);
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.pacientePesquisa = null;
            this.pagination.current_page = 1;
            this.search();
        },

        novoPaciente() {
            this.$store.dispatch("MdNovoPacienteModule/setPaciente", null);
            this.$store.dispatch("MdNovoPacienteModule/setShowModal", true);
        },

        selecionar(paciente) {
            this.$store.dispatch("MdNovoPacienteModule/setPaciente", paciente);
            this.$store.dispatch("MdNovoPacienteModule/setShowModal", true);
        },

        descricaoTabelaGenerica(lista, colunaId) {
            let item = lista.find(item => Number(item.COLUNA_ID) === Number(colunaId));
            return item ? item.DESCRICAO : "-";
        },

        calcularIdadeCompleta(data) {
            if (!data) return "-";

            let nascimento = moment(data);
            let hoje = moment();
            let anos = hoje.diff(nascimento, "years");

            nascimento.add(anos, "years");

            let meses = hoje.diff(nascimento, "months");

            return `${anos} ano${anos !== 1 ? "s" : ""} e ${meses} ${meses !== 1 ? "meses" : "mês"}`;
        },

        mascararCpf(cpf) {
            if (!cpf) return "-";

            let numeros = cpf.replace(/\D/g, "");
            if (numeros.length !== 11) return cpf;

            return `***.***.${numeros.substring(6, 9)}-**`;
        },
    }
}
</script>

<style></style>