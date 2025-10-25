<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="800" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar light elevation="1" class="flex-grow-0" color="primary" dark>
                        <v-toolbar-title>Detalhes do Usuário</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="fullScreen = true" v-show="fullScreen === false">
                            <v-icon>mdi-window-maximize</v-icon>
                        </v-btn>
                        <v-btn icon @click="fullScreen = false" v-show="fullScreen === true">
                            <v-icon>mdi-window-restore</v-icon>
                        </v-btn>
                        <v-btn icon @click="clearFormAndClose">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>

                    <perfect-scrollbar>
                        <v-tabs v-model="tab" background-color="primary" dark icons-and-text grow>

                            <v-tab key="TabUsuario">
                                USUÁRIO
                            </v-tab>

                            <v-tab key="TabUsuarioPerfil" :disabled="usuario.USUARIO_ID === null">
                                PERFIS
                            </v-tab>

                            <v-tabs-items v-model="tab">
                                <v-tab-item key="TabUsuario">
                                    <TabUsuario ref="TabUsuario"></TabUsuario>
                                </v-tab-item>
                                <v-tab-item key="TabUsuarioPerfil">
                                    <TabUsuarioPerfil ref="TabUsuarioPerfil"></TabUsuarioPerfil>
                                </v-tab-item>
                            </v-tabs-items>
                        </v-tabs>
                    </perfect-scrollbar>

                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import TabUsuario from "./TabUsuario";
import TabUsuarioPerfil from "./TabUsuarioPerfil";

export default {
    name: "MdNovoUsuario",
    components: { TabUsuarioPerfil, TabUsuario },
    data() {
        return {
            tab: 0,
            msgId: 'msgTabUsuario',
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        showModal: {
            get() { return this.$store.getters['MdNovoUsuarioModule/getShowModal'] },
            set(newValue) { this.$store.dispatch('MdNovoUsuarioModule/setShowModal', newValue) }
        },
        fullScreen: {
            get() { return this.$store.getters['MdNovoUsuarioModule/getFullScreen'] },
            set(newValue) { this.$store.dispatch('MdNovoUsuarioModule/setFullScreen', newValue) }
        },
        usuario: {
            get() { return this.$store.getters['MdNovoUsuarioModule/getUsuario'] },
            set(newValue) { this.$store.dispatch('MdNovoUsuarioModule/setUsuario', newValue) }
        },
    },
    methods: {
        clearFormAndClose() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId);
            if (typeof this.$refs.TabUsuarioPerfil != "undefined")
                this.$refs.TabUsuarioPerfil.clearForm();
            this.usuario = null;
            this.showModal = false;
            this.tab = 0;
            this.$refs.TabUsuario.clearFormAndClose()
        },
    }
}
</script>

<style scoped></style>