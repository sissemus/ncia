<template>
    <div>
        <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
        <div :id="msgIdDebug"></div>

        <v-card-text class="mt-5">

            <v-row>
                <v-col cols="12">
                    <v-autocomplete label="Unidade*" :items="unidadesSolicitantes" item-value="UNIDADE_ID"
                        item-text="UNIDADE_NOME" v-model="usuarioUnidade.UNIDADE_ID"
                        :menu-props="{ offsetY: true }"></v-autocomplete>
                </v-col>
            </v-row>

            <v-simple-table dense v-show="usuario.usuarioUnidades && usuario.usuarioUnidades.length" class="mb-0 mt-5">
                <template v-slot:default>
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Unidade</th>
                            <th class="text-left">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="usuarioUnidade in (usuario.usuarioUnidades || [])"
                            :key="usuarioUnidade['USUARIO_UNIDADE_ID']">
                            <td>{{ usuarioUnidade["USUARIO_UNIDADE_ID"] }}</td>
                            <td>{{ usuarioUnidade.unidade["UNIDADE_NOME"] }}</td>
                            <td>
                                <v-btn icon @click="deletar(usuarioUnidade)" title="Desvincular">
                                    <v-icon>mdi-delete</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </template>
            </v-simple-table>

        </v-card-text>

        <v-divider class="ma-0"></v-divider>

        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" dark tile @click="salvar">Vincular Unidade</v-btn>
        </v-card-actions>

    </div>
</template>

<script>
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import Swal from "sweetalert2";

export default {
    name: "TabUsuarioUnidade",
    components: { TratarErroAjax },
    data() {
        return {
            msgId: 'msgTabUsuarioUnidade',
            msgIdDebug: 'msgTabUsuarioUnidadeDebug',
            unidades: []
        }
    },
    mounted() {
        this.listarUnidades();
    },
    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl",
            listar: 'UsuarioViewModule/getListar'
        }),
        usuario: {
            get() {
                return this.$store.getters["MdNovoUsuarioModule/getUsuario"]
            },
            set(newValue) {
                this.$store.dispatch("MdNovoUsuarioModule/setUsuario", newValue)
            }
        },
        usuarioUnidade: {
            get() {
                return this.$store.getters["TabUsuarioUnidadeModule/getUsuarioUnidade"]
            },
            set(newValue) {
                this.$store.dispatch("TabUsuarioUnidadeModule/setUsuarioUnidade", newValue)
            }
        },
        unidadesSolicitantes() {
            return (this.unidades || []).filter(unidade =>
                Number(unidade.UNIDADE_SOLICITANTE) === 1 &&
                Number(unidade.UNIDADE_ATIVO) === 1
            );
        },
    },
    methods: {
        listarUnidades() {
            axios.get(`${this.baseUrl}/unidade/listar`)
                .then(r => {
                    this.unidades = r.data;
                })
                .catch(e => {
                    console.error("ERRO: ", e);
                })
        },

        salvar() {
            this.usuarioUnidade.USUARIO_ID = this.usuario.USUARIO_ID;

            let payload = JSON.parse(JSON.stringify(this.usuarioUnidade));

            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);

            axios({
                method: "POST",
                url: `${this.baseUrl}/usuario_unidade/inserir`,
                data: payload,
            })
                .then(r => {
                    this.usuario = r.data.retorno;
                    this.clearForm();
                    Swal.fire("Sucesso", "Unidade vinculada com sucesso", "success");
                    this.listar();
                })
                .catch(e => {
                    console.error("ERRO: ", e);
                    this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                        id: this.msgId,
                        response: e.response,
                    });
                });
        },

        deletar(dado) {
            let params = dado;

            axios.delete(`${this.baseUrl}/usuario_unidade/deletar`, { params })
                .then(r => {
                    this.usuario = r.data.retorno;
                    this.clearForm();
                    Swal.fire("Sucesso", "Unidade desvinculada com sucesso", "success");
                    this.listar();
                })
                .catch(e => {
                    console.error("ERRO: ", e);
                    this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                        id: this.msgId,
                        response: e.response,
                    });
                });
        },

        clearForm() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
            this.usuarioUnidade = null;
        }
    }
}
</script>

<style scoped></style>