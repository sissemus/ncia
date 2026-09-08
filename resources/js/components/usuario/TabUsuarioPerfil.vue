<template>
    <div>
        <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
        <div :id="msgIdDebug"></div>

        <v-card-text class="mt-5">

            <v-row>
                <v-col cols="9" class="mb--2">
                    <v-text-field
                        label="Perfil*"
                        autocomplete="off"
                        readonly
                        hide-details
                        :value="perfil ? perfil.PERFIL_NOME : ''"
                        append-icon="mdi-magnify"
                        @click:append="selecionarPerfil"
                    ></v-text-field>
                </v-col>
                <v-col cols="3">
                    <v-select
                        label="Ativo*"
                        :items="ativos"
                        :item-value="'id'"
                        :item-text="'text'"
                        v-model="usuarioPerfil.USUARIO_PERFIL_ATIVO">
                    </v-select>
                </v-col>
            </v-row>

            <v-simple-table dense v-show="usuario.usuarioPerfis.length" class="mb-0 mt-5">
                <template v-slot:default>
                    <thead>
                    <tr>
                        <th class="text-left">ID</th>
                        <th class="text-left">Perfil</th>
                        <th class="text-left">Ativo</th>
                        <th class="text-left">Ação</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="usuarioPerfil in usuario.usuarioPerfis" :key="usuarioPerfil['USUARIO_PERFIL_ID']">
                        <td>{{ usuarioPerfil["USUARIO_PERFIL_ID"] }}</td>
                        <td>{{ usuarioPerfil.perfil["PERFIL_NOME"] }}</td>
                        <td>
                            <v-chip x-small v-if="usuarioPerfil['USUARIO_PERFIL_ATIVO'] === 1" color="green" dark>Sim</v-chip>
                            <v-chip x-small v-else color="red" dark>Não</v-chip>
                        </td>
                        <td>
                            <v-btn icon small @click="buscar(usuarioPerfil)">
                                <v-icon>mdi-pencil</v-icon>
                            </v-btn>
                            <v-btn icon @click="deletar(usuarioPerfil)" title="Remover">
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
            <v-btn v-show="!usuario.usuarioPerfis.length > 0" color="primary" dark tile @click="salvar">Vincular Perfil</v-btn>
            <v-btn v-show="usuarioPerfil.USUARIO_PERFIL_ID" color="primary" dark tile @click="clearForm">
                cancelar
            </v-btn>
        </v-card-actions>

    </div>
</template>

<script>
    import {mapGetters} from "vuex";
    import TratarErroAjax from "../assets/TratarErroAjax";
    import Swal from "sweetalert2";

    export default {
        name: "TabUsuarioPerfil",
        components: {TratarErroAjax},

        data() {
            return {
                msgId: 'msgTabUsuarioPerfil',
                msgIdDebug: 'msgTabUsuarioPerfilDebug',
                ativos: [
                    {
                        id: 1,
                        text: 'Sim'
                    },
                    {
                        id: 0,
                        text: 'Não'
                    }
                ],
            };
        },

        computed: {
            ...mapGetters({
                baseUrl: "getBaseUrl",
                usuario: "MdNovoUsuarioModule/getUsuario",
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

            usuarioPerfil: {
                get() {
                    return this.$store.getters["TabUsuarioPerfilModule/getUsuarioPerfil"]
                },
                set(newValue) {
                    this.$store.dispatch("TabUsuarioPerfilModule/setUsuarioPerfil", newValue)
                }
            },

            perfil: {
                get() {
                    return this.$store.getters['TabUsuarioPerfilModule/getPerfil']
                },
                set(newValue) {
                    this.$store.dispatch('TabUsuarioPerfilModule/setPerfil', newValue)
                }
            },
        },

        methods: {

            selecionarPerfil() {
                this.$store.dispatch(
                    "MdSelecionarPerfilModule/setModulo",
                    "TabUsuarioPerfilModule/setPerfil"
                );
                this.$store.dispatch(
                    "MdSelecionarPerfilModule/setShowModal",
                    true
                );
                this.$store.dispatch(
                    "MdSelecionarPerfilModule/setQuemChamou",
                    "TabUsuarioPerfil"
                );
            },

            salvar() {
                this.usuarioPerfil.PERFIL_ID = this.perfil.PERFIL_ID;
                this.usuarioPerfil.USUARIO_ID = this.usuario.USUARIO_ID;
                let payload = JSON.parse(JSON.stringify(this.usuarioPerfil));

                this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
                axios({
                    method: this.usuarioPerfil.USUARIO_PERFIL_ID === null ? "POST" : "PUT",
                    url:
                        this.usuarioPerfil.USUARIO_PERFIL_ID === null
                            ? `${this.baseUrl}/usuario_perfil/inserir`
                            : `${this.baseUrl}/usuario_perfil/alterar`,
                    data: payload,
                })
                    .then((r) => {
                        this.usuario = r.data.retorno;
                        this.clearForm();
                        Swal.fire("Sucesso", "Salvo com sucesso", "success");
                        if (typeof this.listar === 'function') this.listar();
                    })
                    .catch((e) => {
                        console.error("ERRO: ", e);
                        this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                            id: this.msgId,
                            response: e.response,
                        });
                    });
            },

            buscar(usuarioPerfil) {
                this.usuarioPerfil = usuarioPerfil;
                this.perfil = usuarioPerfil.perfil;
            },

            deletar(dado) {
                let params = dado;
                axios.delete(`${this.baseUrl}/usuario_perfil/deletar`, {params})
                    .then(r => {
                        this.usuario = r.data.retorno;
                        this.clearForm();
                        Swal.fire("Sucesso", "Removido com sucesso", "success");
                        if (typeof this.listar === 'function') this.listar();
                    })
                    .catch((e) => {
                        console.error("ERRO: ", e);
                        this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                            id: this.msgId,
                            response: e.response,
                        });
                    });
            },

            clearForm() {
                this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId);
                this.usuarioPerfil = null;
                this.perfil = null;
            },
        }
    }
</script>

<style scoped>

</style>
