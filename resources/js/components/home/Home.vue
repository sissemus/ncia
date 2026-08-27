<template>
    <div>
        <v-card class="elevation-2 rounded-lg">
            <v-toolbar flat dense class="elevation-1">
                <v-icon color="primary" class="mr-2">mdi-clipboard-check-outline</v-icon>
                <v-toolbar-title class="font-weight-bold">Chamados em aberto</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon color="primary" @click="carregar"><v-icon>mdi-refresh</v-icon></v-btn>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <v-card-text>
                <v-alert v-if="!carregando && !chamados.length" type="info" outlined>Nenhum chamado aberto.</v-alert>
                <v-simple-table v-else dense>
                    <thead><tr><th>Nº Chamado</th><th>Paciente</th><th>Data/Hora</th><th>Procedimento</th><th>Prioridade</th><th>Ação</th></tr></thead>
                    <tbody>
                        <tr v-for="chamado in chamados" :key="chamado.CHAMADO_ID">
                            <td>{{ chamado.CHAMADO_ID }}</td>
                            <td>{{ chamado.paciente ? chamado.paciente.PACIENTE_NOME : '-' }}</td>
                            <td>{{ formatarDataHora(chamado.CHAMADO_DATA) }}</td>
                            <td>{{ chamado.procedimentos && chamado.procedimentos.length ? chamado.procedimentos[0].PROCEDIMENTO_DESCRICAO : '-' }}</td>
                            <td><v-chip x-small dark :color="corPrioridade(chamado.TG_PRIORIDADE_ID)">{{ descricao(prioridades, chamado.TG_PRIORIDADE_ID) }}</v-chip></td>
                            <td class="text-center"><v-btn icon color="primary" title="Analisar chamado" @click="recepcionar(chamado.CHAMADO_ID)"><v-icon>mdi-clipboard-search-outline</v-icon></v-btn></td>
                        </tr>
                    </tbody>
                </v-simple-table>
                <v-pagination v-if="pagination.total" class="mt-4" v-model="pagination.current_page" :length="pagination.last_page" @input="carregar"></v-pagination>
            </v-card-text>
        </v-card>
    </div>
</template>


<script>
import TratarErroAjax from "../assets/TratarErroAjax";
import { mapGetters } from "vuex";

export default {
    name: "Home",
    components: { TratarErroAjax },
    props: {
        usuario_logado: {
            type: Object,
        },
        prioridades: {
            type: Array,
            default: () => [],
        },
        alerta_analise: {
            type: Object,
        },
    },
    data() {
        return {
            msgId: 'msgHomeChamados',
            chamados: [],
            carregando: false,
            pagination: { current_page: 1, total: 0, last_page: 1 }
        }
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
        }),
    },
    mounted() {
        this.$store.dispatch('AuthModule/setUsuario', this.usuario_logado)
        this.carregar()
    },
    methods: {
        carregar() {
            this.carregando = true
            axios.get(`${this.baseUrl}/home/chamados-abertos`, { params: { page: this.pagination.current_page } })
                .then(r => { this.chamados = r.data.data; this.pagination = { current_page: r.data.current_page, total: r.data.total, last_page: r.data.last_page } })
                .catch(e => this.erro(e))
                .finally(() => { this.carregando = false })
        },
        recepcionar(id) {
            axios.post(`${this.baseUrl}/chamado_analisar/recepcionar`, { CHAMADO_ID: id })
                .then(() => { window.location.href = `${this.baseUrl}/chamado_analisar?chamado=${id}` })
                .catch(e => this.erro(e))
        },
        descricao(lista, id) { const item = (lista || []).find(x => Number(x.COLUNA_ID) === Number(id)); return item ? item.DESCRICAO : '-' },
        corPrioridade(id) { const d = this.descricao(this.prioridades, id).toUpperCase(); return d.indexOf('VERMELHO') >= 0 ? 'red' : d.indexOf('LARANJA') >= 0 ? 'orange' : d.indexOf('AMARELO') >= 0 ? 'yellow darken-2' : 'green' },
        formatarDataHora(data) { return data ? new Date(data).toLocaleString('pt-BR') : '-' },
        erro(e) { this.$store.dispatch('TratarErroAjaxModule/tratarErro', { id: this.msgId, response: e.response }, { root: true }) }
    },

}
</script>

<style scoped></style>
