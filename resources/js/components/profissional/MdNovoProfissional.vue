<template>
    <v-dialog v-model="showModal" persistent width="900" scrollable :fullscreen="fullScreen">
        <v-card>
            <v-toolbar light elevation="1" class="flex-grow-0" color="primary" dark>
                <v-toolbar-title>Detalhes do Profissional</v-toolbar-title>
                <v-spacer></v-spacer>

                <v-btn icon @click="fullScreen = true" v-show="fullScreen === false">
                    <v-icon>mdi-window-maximize</v-icon>
                </v-btn>

                <v-btn icon @click="fullScreen = false" v-show="fullScreen === true">
                    <v-icon>mdi-window-restore</v-icon>
                </v-btn>

                <v-btn icon :disabled="salvando" @click="clearFormAndClose">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-toolbar>

            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <v-card-text class="mt-5">
                <v-row>
                    <v-col cols="12" md="8">
                        <v-text-field label="Nome*" autocomplete="off"
                            v-model="profissional.PROFISSIONAL_NOME"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-text-field label="CPF*" v-mask="'###.###.###-##'" autocomplete="off"
                            v-model="profissional.PROFISSIONAL_CPF" @blur="consultarUsuario"></v-text-field>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="12" md="4">
                        <v-text-field label="Data de Nascimento*" type="date" autocomplete="off"
                            v-model="profissional.PROFISSIONAL_NASCIMENTO"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-select label="Sexo*" :items="sexos" item-value="COLUNA_ID" item-text="DESCRICAO"
                            clearable v-model="profissional.TG_SEXO_ID"></v-select>
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-select label="Tipo de Profissional*" :items="tiposProfissional"
                            item-value="COLUNA_ID" item-text="DESCRICAO"
                            v-model="profissional.TG_TIPO_PROFISSIONAL_ID"></v-select>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="12" md="6">
                        <v-text-field label="Telefone de contato*" v-mask="['(##) ####-####', '(##) #####-####']" autocomplete="tel"
                            inputmode="tel" hint="Informe DDD e telefone. Preferencialmente celular com WhatsApp."
                            persistent-hint required clearable v-model="profissional.PROFISSIONAL_WHATSAPP"></v-text-field>
                    </v-col>

                    <v-col cols="12" md="6">
                        <v-select label="Ativo*" :items="ativos" item-value="id" item-text="text"
                            v-model="profissional.PROFISSIONAL_ATIVO"></v-select>
                    </v-col>
                </v-row>
                <v-alert v-if="profissional.usuario" type="info" outlined>
                    Usuário vinculado: {{ profissional.usuario.USUARIO_LOGIN }}.
                    Nome, CPF e telefone são atualizados nos dois cadastros.
                </v-alert>
                <template v-else-if="podeGerenciarAcesso">
                    <v-switch v-model="criarAcesso" label="Criar acesso ao sistema"
                        :disabled="salvando" @change="consultarUsuario"></v-switch>
                    <template v-if="criarAcesso">
                        <v-progress-linear v-if="consultando" indeterminate></v-progress-linear>
                        <template v-if="usuarioEncontrado">
                            <v-alert :type="usuarioEncontrado.vinculado ? 'warning' : 'info'" outlined>
                                Este CPF já possui o usuário {{ usuarioEncontrado.USUARIO_LOGIN }}
                                ({{ usuarioEncontrado.USUARIO_NOME }}).
                                {{ usuarioEncontrado.USUARIO_ATIVO ? '' : 'A conta está inativa e permanecerá inativa.' }}
                                {{ usuarioEncontrado.vinculado ? 'A conta já está vinculada a outro profissional.' : 'Login, senha e permissões serão preservados; nome e telefone serão atualizados com os dados deste formulário.' }}
                            </v-alert>
                            <v-checkbox v-if="!usuarioEncontrado.vinculado" v-model="confirmarVinculo"
                                label="Confirmo o vínculo com este usuário"></v-checkbox>
                        </template>
                        <template v-else>
                            <v-select label="Perfil de acesso*" :items="perfis" item-value="PERFIL_ID"
                                item-text="PERFIL_NOME" v-model="perfilId" required></v-select>
                        </template>
                    </template>
                </template>
            </v-card-text>

            <v-divider class="ma-0"></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" tile :loading="salvando" :disabled="consultando || salvando" @click="salvar">
                    Salvar Profissional
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import UtilsMixins from "../../mixins/UtilsMixins";

export default {
    name: "MdNovoProfissional",
    components: { TratarErroAjax },
    mixins: [UtilsMixins],
    props: {
        sexos: {
            type: Array,
            default: () => []
        },
        tiposProfissional: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            msgId: "msgMdNovoProfissional",
            msgIdDebug: "msgMdNovoProfissionalDebug",
            criarAcesso: false,
            perfilId: null,
            perfis: [],
            podeGerenciarAcesso: false,
            usuarioEncontrado: null,
            confirmarVinculo: false,
            cpfConsultado: null,
            consultando: false,
            salvando: false,
            consultaId: 0,
            ativos: [
                { id: 1, text: "Sim" },
                { id: 0, text: "Não" }
            ]
        }
    },
    watch: {
        showModal(aberto) {
            this.criarAcesso = false
            this.perfilId = null
            this.usuarioEncontrado = null
            this.confirmarVinculo = false
            this.cpfConsultado = null
            this.consultaId++
            this.consultando = false
            if (aberto) this.carregarOpcoes()
        },
        'profissional.PROFISSIONAL_CPF'() {
            this.cpfConsultado = null
            this.usuarioEncontrado = null
            this.confirmarVinculo = false
            this.consultaId++
            this.consultando = false
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: "getBaseUrl"
        }),
        profissional: {
            get() {
                return this.$store.getters["MdNovoProfissionalModule/getProfissional"]
            },
            set(newValue) {
                this.$store.dispatch("MdNovoProfissionalModule/setProfissional", newValue)
            }
        },
        showModal: {
            get() {
                return this.$store.getters["MdNovoProfissionalModule/getShowModal"]
            },
            set(newValue) {
                this.$store.dispatch("MdNovoProfissionalModule/setShowModal", newValue)
            }
        },
        fullScreen: {
            get() {
                return this.$store.getters["MdNovoProfissionalModule/getFullScreen"]
            },
            set(newValue) {
                this.$store.dispatch("MdNovoProfissionalModule/setFullScreen", newValue)
            }
        }
    },
    methods: {
        tratarErro(e) {
            this.$store.dispatch("TratarErroAjaxModule/tratarErro", {
                id: this.msgId, response: e.response
            })
        },
        async carregarOpcoes() {
            this.podeGerenciarAcesso = false
            try {
                const r = await axios.get(`${this.baseUrl}/profissional/acesso-opcoes`)
                if (!this.showModal) return
                this.perfis = r.data.retorno.perfis
                this.podeGerenciarAcesso = r.data.retorno.podeGerenciarAcesso
                this.criarAcesso = this.podeGerenciarAcesso && this.profissional.PROFISSIONAL_ID === null
            } catch (e) { this.tratarErro(e) }
        },
        async consultarUsuario() {
            if (!this.criarAcesso || this.profissional.USUARIO_ID) return false
            const cpf = (this.profissional.PROFISSIONAL_CPF || '').replace(/\D/g, '')
            if (cpf.length !== 11) return false
            const id = ++this.consultaId
            this.consultando = true
            try {
                const r = await axios.get(`${this.baseUrl}/profissional/usuario-por-cpf`, { params: { CPF: cpf } })
                if (id !== this.consultaId) return false
                if (!this.usuarioEncontrado || !r.data.retorno || this.usuarioEncontrado.USUARIO_ID !== r.data.retorno.USUARIO_ID) {
                    this.confirmarVinculo = false
                }
                this.usuarioEncontrado = r.data.retorno
                this.cpfConsultado = cpf
                return true
            } catch (e) {
                if (id === this.consultaId) {
                    this.cpfConsultado = null
                    this.tratarErro(e)
                }
                return false
            } finally {
                if (id === this.consultaId) this.consultando = false
            }
        },
        async salvar() {
            if (this.salvando) return
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId)

            if (this.criarAcesso && !this.profissional.USUARIO_ID) {
                const cpf = (this.profissional.PROFISSIONAL_CPF || '').replace(/\D/g, '')
                if (cpf.length !== 11) {
                    Swal.fire("Atenção", "Informe um CPF válido para consultar o acesso.", "warning")
                    return
                }
                if (this.cpfConsultado !== cpf && !(await this.consultarUsuario())) return
                if (this.usuarioEncontrado && (this.usuarioEncontrado.vinculado || !this.confirmarVinculo)) {
                    Swal.fire("Atenção", "Confirme o vínculo com uma conta disponível para continuar.", "warning")
                    return
                }
                if (!this.usuarioEncontrado && !this.perfilId) {
                    Swal.fire("Atenção", "Selecione o perfil de acesso.", "warning")
                    return
                }
            }

            let payload = JSON.parse(JSON.stringify(this.profissional))
            payload.CRIAR_ACESSO = this.criarAcesso
            payload.PERFIL_ID = this.perfilId
            payload.VINCULAR_USUARIO_ID = this.confirmarVinculo && this.usuarioEncontrado
                ? this.usuarioEncontrado.USUARIO_ID : null

            if (payload.PROFISSIONAL_CPF)
                payload.PROFISSIONAL_CPF = payload.PROFISSIONAL_CPF.replace(/\D/g, "")

            payload.PROFISSIONAL_NASCIMENTO = this.formatarDataSQL(
                payload.PROFISSIONAL_NASCIMENTO
            )

            this.salvando = true
            axios({
                method: payload.PROFISSIONAL_ID === null ? "POST" : "PUT",
                url: payload.PROFISSIONAL_ID === null
                    ? `${this.baseUrl}/profissional/inserir`
                    : `${this.baseUrl}/profissional/alterar`,
                data: payload
            })
                .then(r => {
                    const usuario = r.data.retorno.usuario
                    const mensagem = r.data.usuario_criado && usuario
                        ? `Profissional salvo. Login: ${usuario.USUARIO_LOGIN}. Senha inicial: 123. Troca obrigatória no acesso.`
                        : "Profissional salvo com sucesso"
                    Swal.fire("Sucesso", mensagem, "success")
                    this.clearFormAndClose()
                    this.$emit("salvo")
                })
                .catch(e => {
                    this.tratarErro(e)
                })
                .finally(() => { this.salvando = false })
        },

        clearForm() {
            this.$store.dispatch("MdNovoProfissionalModule/setProfissional", null)
        },

        clearFormAndClose() {
            this.$store.dispatch("TratarErroAjaxModule/fecharAlert", this.msgId)
            this.clearForm()
            this.fullScreen = false
            this.showModal = false
        }
    }
}
</script>

<style scoped></style>
