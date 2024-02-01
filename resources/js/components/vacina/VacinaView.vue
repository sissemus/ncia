<template>
    <div>
        <v-card>
            <div>
                <v-toolbar class="elevation-1">
                    <v-icon class="mr-1">mdi-database</v-icon>
                    <v-toolbar-title>Cadastro de Vacinas</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                <div :id="msgIdDebug"></div>
                <v-card-text>
                    <v-row>
                        <v-col cols="2">
                            <label>Id</label>
                            <v-text-field
                                hide-details
                                solo
                                readonly
                                v-model="vacina['VACINA_ID']"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="10">
                            <label>Nome da Vacina</label>
                            <v-text-field
                                hide-details
                                solo
                                v-model="vacina['VACINA_NOME']"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" dark outlined tile @click="salvar">
                        SALVAR
                    </v-btn>
                </v-card-actions>
            </div>
        </v-card>

        <v-card>
            <v-card-text>
                <v-simple-table  v-show="vacinas.length" class="mb-0" dense="">
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Nome da Vacina</th>
                            <th class="text-left">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="vacina in vacinas" :key="vacina['VACINA_ID']" @click="selecionar(vacina)" style="cursor: pointer">
                            <td>{{ vacina['VACINA_ID'] }}</td>
                            <td>{{ vacina['VACINA_NOME'] }}</td>
                            <td>
                                <v-btn icon><v-icon>mdi-pencil</v-icon></v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import {mapActions, mapGetters} from "vuex";
import Swal from "sweetalert2";

export default {
name: "VacinaView",
    components: {TratarErroAjax},
    data() {
        return {
            msgId: 'msgVacinaView',
            msgIdDebug: 'msgVacinaViewDebug'
        }
    },
    mounted() {
        this.search()
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        vacina: {
            get() { return this.$store.getters['VacinaViewModule/getVacina'] },
            set(newValue) { this.$store.dispatch('VacinaViewModule/setVacina', newValue) }
        },
        vacinas: {
            get() { return this.$store.getters['VacinaViewModule/getVacinas'] },
            set(newValue) { this.$store.dispatch('VacinaViewModule/setVacinas', newValue) }
        },
    },
    methods: {
        salvar() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.vacina['VACINA_ID'] === null ? 'POST' : 'PUT',
                url: this.vacina['VACINA_ID'] === null ? `${this.baseUrl}/vacina/create` : `${this.baseUrl}/vacina/update`,
                data: this.vacina
            }).then(r => {
                this.vacina = null
                Swal.fire("Sucesso", "Salvo com sucesso", "success").then(() => {
                    this.search()
                })
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
                this.$vuetify.goTo(0)
            })
        },
        search() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'get',
                url: `${this.baseUrl}/vacina/search`,
                params: {
                    page: 0,
                }
            }).then(r => {
                this.vacinas = r.data
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
                this.$vuetify.goTo(0)
            })
        },
        selecionar(vacina) {
            this.vacina = vacina
        }
    }
}

</script>

<style scoped>

</style>
