<template>
    <div>
        <v-card>
            <div>
                <v-toolbar class="elevation-1">
                    <v-icon class="mr-1">mdi-database</v-icon>
                    <v-toolbar-title>Cadastro de Doses</v-toolbar-title>
                    <v-spacer></v-spacer>
                </v-toolbar>
                <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                <div :id="msgIdDebug"></div>
                <v-card-text>
                    <v-row>
                        <v-col cols="2">
                            <label>Id da Dose</label>
                            <v-text-field
                                hide-details
                                solo
                                disabled
                                v-model="dose.DOSE_ID"
                            ></v-text-field>
                        </v-col>
                        <v-col>
                            <label>Nome da Dose</label>
                            <v-text-field
                                hide-details
                                solo
                                v-model="dose.DOSE_NOME"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" dark outlined tile @click="save()">
                        SALVAR
                    </v-btn>
                </v-card-actions>
            </div>
        </v-card>
        <v-card>
            <v-card-text>
                <v-simple-table  v-show="doses.length" class="mb-0">
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th class="text-left">Id</th>
                            <th class="text-left">Nome da Dose</th>
                            <th class="text-left">Ações</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="dose in doses" :key="dose['DOSE_ID']">
                            <td>{{ dose['DOSE_ID'] }}</td>
                            <td>{{ dose['DOSE_NOME'] }}</td>
                            <td>
                                <v-btn color="danger" title="Editar" icon @click="selecionar(dose)"><v-icon>mdi-pencil</v-icon></v-btn>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </v-card-text>
            <v-card-actions>
                <v-row>
                    <v-col>
                        <v-pagination
                            v-show="pagination.total"
                            v-model="pagination.current_page"
                            :length="pagination.last_page"
                            total-visible="10"
                            @input="onPageChange"
                        ></v-pagination>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>
    </div>
</template>

<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import {mapActions, mapGetters} from "vuex";
import Swal from "sweetalert2";

export default {
    name: "DoseView",
    components: {TratarErroAjax},
    data() {
        return {
            msgId: 'msgDoseView',
            msgIdDebug: 'msgDoseViewDebug'
        }
    },
    mounted() {
        this.search()
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        dose: {
            get() { return this.$store.getters['DoseViewModule/getDose'] },
            set(newValue) { this.$store.dispatch('DoseViewModule/setDose', newValue) }
        },
        doses: {
            get() { return this.$store.getters['DoseViewModule/getDoses'] },
            set(newValue) { this.$store.dispatch('DoseViewModule/setDoses', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['DoseViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('DoseViewModule/setPagination', newValue) }
        }
    },
    methods: {
        onPageChange() {
            this.search()
        },
        clearForm(showModal = false) {
            this.showModal = showModal
        },
        save() {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: this.dose.DOSE_ID === null ? 'POST' : 'PUT',
                url: this.dose.DOSE_ID === null ? `${this.baseUrl}/dose/create` : `${this.baseUrl}/dose/update`,
                data: this.dose
            }).then(r => {
                this.dose = null;
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
                url: `${this.baseUrl}/dose/search`,
                params: {
                    doseNome: '',
                    page: this.pagination.current_page
                }
            }).then(r => {
                this.doses = r.data.data
                this.pagination = r.data
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
                this.$vuetify.goTo(0)
            })
        },
        selecionar(dose) {
            this.dose = dose
        }
    }
}

</script>

<style scoped>

</style>
