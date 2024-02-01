<script>
import { Line, mixins } from 'vue-chartjs'
import {mapGetters} from "vuex"
const { reactiveData } = mixins;
export default {
    extends: Line,
    mixins: [reactiveData],
    data() {
        return {
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Hora'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Situação'
                        },
                        ticks: {
                            min: 1,
                            max: 4,
                            stepSize: 1,
                            callback: function (label, index, labels) {
                                switch (parseInt(label)) {
                                    case 1:
                                        return "Sem fila";
                                    case 2:
                                        return "Pouca fila";
                                    case 3:
                                        return "Moderada";
                                    case 4:
                                        return "Intensa";
                                }
                            }
                        },
                        gridLines: {
                            display: true
                        }
                    }]
                }
            }
        }
    },
    mounted () {
        this.renderChart(this.chartdata, this.options)
    },
    computed: {
        ...mapGetters({
            chartdata: 'MdGraficoSituacaoFilaModule/getChartdata',
        })
    },
    watch: {
        chartdata (nv) {
            this.renderChart(nv, this.options)
        },
        // options (ov) {
        //     this.renderChart(this.chartdata, this.options)
        // }
    }
}
</script>

<style scoped>

</style>
