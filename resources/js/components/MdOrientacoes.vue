<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="1000" scrollable :fullscreen="fullScreen">
                <v-card>
                    <v-toolbar light elevation="1" class="flex-grow-0 mb-3">
                        <v-toolbar-title>Informativos</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="clearForm(false)">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>
                    <div :id="msgIdDebug"></div>
                    <v-card-text class="pb-0">
                        <v-alert
                            dense
                            outlined
                            type="info"
                            v-for="(orientacao, i) in orientacoes" :key="i">{{ orientacao['ORIENTACAO_MENSAGEM'] }}</v-alert>
                    </v-card-text>
                    <v-divider class="py-0 my-0"></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" dark outlined tile @click="clearForm(false)">
                            fechar
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
name: "MdOrientacoes",
    data() {
        return {
            msgId: 'msgMdOrientacoes',
            msgIdDebug: 'msgMdOrientacoesDebug'
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        showModal: {
            get() { return this.$store.getters['MdOrientacoesModule/getShowModal'] },
            set(newValue) { this.$store.dispatch('MdOrientacoesModule/setShowModal', newValue) }
        },
        fullScreen: {
            get() { return this.$store.getters['MdOrientacoesModule/getFullScreen'] },
            set(newValue) { this.$store.dispatch('MdOrientacoesModule/setFullScreen', newValue) }
        },
        orientacoes: {
            get() { return this.$store.getters['MdOrientacoesModule/getOrientacoes'] },
            set(newValue) { this.$store.dispatch('MdOrientacoesModule/setOrientacoes', newValue) }
        }
    },
    methods: {
        clearForm(showModal = false) {
            this.showModal = showModal
        }
    }
}

</script>

<style scoped>

</style>
