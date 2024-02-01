<template>
    <div>
        <v-row justify="center">
            <v-dialog v-model="showModal" persistent width="750" scrollable>
                <v-card>
                    <v-toolbar light elevation="1" class="flex-grow-0 mb-3">
                        <v-toolbar-title>Gráfico da Fila - {{ local['LOCAL_DESCRICAO'] === null ? '' : local['LOCAL_DESCRICAO'] }}</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="clearForm(false)">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </v-toolbar>
                    <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                    <div :id="msgIdDebug"></div>
                    <v-card-text>
                        <v-row>
                            <v-col>
                                <v-menu
                                    v-model="menu2"
                                    :close-on-content-click="false"
                                    :nudge-right="40"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="auto"
                                >
                                    <template v-slot:activator="{ on, attrs }">
                                        <label>Selecione a data</label>
                                        <v-text-field
                                            v-model="date"
                                            label="Selecione a data"
                                            prepend-icon="mdi-calendar"
                                            readonly
                                            v-bind="attrs"
                                            v-on="on"
                                            type="date"
                                            solo
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker
                                        v-model="date"
                                        @input="menu2 = false"
                                        locale="pt-BR"
                                        @change="onDayChange"
                                    ></v-date-picker>
                                </v-menu>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <LineChart></LineChart>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-divider></v-divider>
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
import TratarErroAjax from "../assets/TratarErroAjax";
import {mapActions, mapGetters} from "vuex";

export default {
    name: "MdGraficoSituacaoFila",
    components: {TratarErroAjax},
    data() {
        return {
            msgId: 'msgMdGraficoSituacaoFila',
            msgIdDebug: 'msgMdGraficoSituacaoFilaDebug',
            picker: new Date().toISOString().substr(0, 10),
            date: new Date().toISOString().substr(0, 10),
            modal: false,
            menu2: false,
            gradient: null,
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        showModal: {
            get() {return this.$store.getters['MdGraficoSituacaoFilaModule/getShowModal']},
            set(newValue) {this.$store.dispatch('MdGraficoSituacaoFilaModule/setShowModal', newValue)}
        },
        fullScreen: {
            get() {return this.$store.getters['MdGraficoSituacaoFilaModule/getFullScreen']},
            set(newValue) {this.$store.dispatch('MdGraficoSituacaoFilaModule/setFullScreen', newValue)}
        },
        options: {
            get() {return this.$store.getters['MdGraficoSituacaoFilaModule/getOptions']},
            set(newValue) {this.$store.dispatch('MdGraficoSituacaoFilaModule/setOptions', newValue)}
        },
        modulo: {
            get() {return this.$store.getters['MdGraficoSituacaoFilaModule/getModulo']},
            set(newValue) {this.$store.dispatch('MdGraficoSituacaoFilaModule/setModulo', newValue)}
        },
        local: {
            get() {return this.$store.getters['MdGraficoSituacaoFilaModule/getLocal']},
            set(newValue) {this.$store.dispatch('MdGraficoSituacaoFilaModule/setLocal', newValue)}
        },
        chartdata: {
            get() {return this.$store.getters['MdGraficoSituacaoFilaModule/getChartdata']},
            set(newValue) {this.$store.dispatch('MdGraficoSituacaoFilaModule/setChartdata', newValue)}
        }
    },
    methods: {
        clearForm(showModal = false) {
            this.showModal = showModal
            // this.modulo()
        },
        teste(el) {
            console.log(el)
        },
        updateChart() {
            console.log('alterando')
        },
        onDayChange(date) {
            this.$store.dispatch('TratarErroAjaxModule/fecharAlert', this.msgId)
            axios({
                method: 'GET',
                url: `${this.baseUrl}/local/buscar`,
                params: {
                    date: date,
                    localId: this.local['LOCAL_ID']
                }
            }).then(r => {
                let situacoes = r.data.grafico.situacoes || [];
                let bc = [];
                console.log(situacoes)
                situacoes.forEach(r => {
                    switch (parseInt(r)) {
                        case 1:
                            bc.push('green')
                            break
                        case 2:
                            bc.push('yellow')
                            break
                        case 3:
                            bc.push('orange')
                            break
                        case 4:
                            bc.push('red')
                            break
                    }
                })
                // return 0
                this.chartdata = {
                    labels: r.data['grafico']['horas'],
                    datasets: [
                        {
                            label: 'Gráfico de Evolução da Fila',
                            data: r.data['grafico']['situacoes'],
                            fill: false,
                            borderWidth: 5,
                            tension: 0,
                            // backgroundColor: [
                            //     'rgb(54, 162, 235)',
                            //     'rgba(54, 162, 235, 0.2)',
                            //     'rgba(255, 99, 132, 0.2)',
                            //     'rgba(255, 159, 64, 0.2)',
                            //     'rgba(255, 205, 86, 0.2)',
                            //     'rgba(75, 192, 192, 0.2)',
                            //     'rgba(153, 102, 255, 0.2)',
                            //     'rgba(201, 203, 207, 0.2)',
                            // ],
                            borderColor: bc
                            // borderColor: [
                            //     'rgb(54, 162, 235)',
                            //     'rgb(255, 99, 132)',
                            //     'rgb(255, 159, 64)',
                            //     'rgb(255, 205, 86)',
                            //     'rgb(75, 192, 192)',
                            //     'rgb(153, 102, 255)',
                            //     'rgb(201, 203, 207)'
                            // ],
                        }
                    ]
                }
            }).catch(e => {
                console.error('ERRO: ', e)
                this.$store.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: this.msgId,
                    response: e.response
                })
            })
        }
    }
}

</script>

<style scoped>

</style>
