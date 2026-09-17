<template>
    <div>
        <v-card outlined class="mb-4">
            <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">
                <v-icon small left color="primary">mdi-notebook-heart-outline</v-icon>
                Atualizações Clínicas
                <v-spacer></v-spacer>
                <v-btn v-if="podeCriar" small color="primary" tile :disabled="carregando || processando"
                    @click="abrirNova">
                    <v-icon small left>mdi-plus</v-icon>
                    Nova Atualização Clínica
                </v-btn>
            </v-card-title>

            <v-card-text class="pt-3">
                <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
                <v-progress-linear v-if="carregando" indeterminate color="primary"></v-progress-linear>
                <v-alert v-else-if="!atualizacoes.length" type="info" dense outlined class="mb-0">
                    Nenhuma atualização clínica registrada para este chamado.
                </v-alert>
                <v-expansion-panels v-else accordion flat>
                    <v-expansion-panel v-for="item in atualizacoesOrdenadas" :key="item.ATUALIZACAO_CLINICA_ID">
                        <v-expansion-panel-header>
                            <div class="w-100">
                                <div class="d-flex align-center flex-wrap">
                                    <strong class="mr-3">Atualização Clínica Nº {{ item.ATUALIZACAO_CLINICA_ID }}</strong>
                                    <v-chip x-small :color="corPrioridade(item.TG_PRIORIDADE_ANTERIOR_ID)" dark class="mr-1"
                                        title="Prioridade anterior">
                                        {{ descricao(prioridades, item.TG_PRIORIDADE_ANTERIOR_ID) }}
                                    </v-chip>
                                    <v-icon x-small class="mx-1">mdi-arrow-right</v-icon>
                                    <v-chip x-small :color="corPrioridade(item.TG_PRIORIDADE_ID)" dark class="mr-3"
                                        title="Nova prioridade">
                                        {{ descricao(prioridades, item.TG_PRIORIDADE_ID) }}
                                    </v-chip>
                                    <span class="caption grey--text text--darken-2">
                                        {{ formatarDataHora(item.ATUALIZACAO_CLINICA_DATA) }}
                                    </span>
                                </div>
                                <div class="caption grey--text text--darken-2 text-truncate mt-1"
                                    :title="item.ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA || '-'">
                                    <strong>Justificativa médica:</strong>
                                    {{ item.ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA || '-' }}
                                </div>
                            </div>
                        </v-expansion-panel-header>
                        <v-expansion-panel-content>
                            <v-row dense>
                                <v-col cols="12" md="4"><v-text-field label="Registrado por" readonly filled dense hide-details :value="nomeUsuario(item)" /></v-col>
                                <v-col cols="12" md="4"><v-text-field label="Profissional responsável" readonly filled dense hide-details :value="item.ATUALIZACAO_CLINICA_PROFISSIONAL" /></v-col>
                                <v-col cols="12" md="4"><v-text-field label="Conselho" readonly filled dense hide-details :value="conselho(item)" /></v-col>
                                <v-col cols="12" md="6"><v-text-field label="Prioridade anterior" readonly filled dense hide-details :value="descricao(prioridades, item.TG_PRIORIDADE_ANTERIOR_ID)" /></v-col>
                                <v-col cols="12" md="6"><v-text-field label="Nova prioridade" readonly filled dense hide-details :value="descricao(prioridades, item.TG_PRIORIDADE_ID)" /></v-col>
                                <v-col cols="12" md="4"><v-text-field label="Precaução" readonly filled dense hide-details :value="descricao(tiposPrecaucao, item.TG_TIPO_PRECAUCAO_ID)" /></v-col>
                                <v-col cols="12" md="4"><v-text-field label="Suporte O2" readonly filled dense hide-details :value="descricao(suportesO2, item.TG_SUPORTE_O2_ID)" /></v-col>
                                <v-col cols="12" md="4"><v-text-field label="Suporte hemodinâmico" readonly filled dense hide-details :value="descricao(suportesHemodinamicos, item.TG_SUPORTE_HEMODINAMICO_ID)" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Temperatura" readonly filled dense hide-details :value="item.ATUALIZACAO_CLINICA_TEMPERATURA" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Pressão arterial" readonly filled dense hide-details :value="item.ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Frequência cardíaca" readonly filled dense hide-details :value="item.ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Saturação O2" readonly filled dense hide-details :value="item.ATUALIZACAO_CLINICA_SATURACAO_O2" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Escala Glasgow" readonly filled dense hide-details :value="item.ATUALIZACAO_CLINICA_ESCALA_GLASGOW || '-'" /></v-col>
                                <v-col cols="12"><v-textarea label="Justificativa médica" readonly filled dense rows="2" auto-grow hide-details :value="item.ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA" /></v-col>
                            </v-row>
                        </v-expansion-panel-content>
                    </v-expansion-panel>
                </v-expansion-panels>
            </v-card-text>
        </v-card>

        <v-dialog v-model="dialog" persistent max-width="1100" scrollable :fullscreen="fullScreen">
            <v-card v-if="dialog">
                <v-toolbar color="primary" dark class="flex-grow-0">
                    <v-toolbar-title>Nova Atualização Clínica — Chamado Nº {{ chamado.CHAMADO_ID }}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon v-if="!fullScreen" @click="fullScreen = true"><v-icon>mdi-window-maximize</v-icon></v-btn>
                    <v-btn icon v-else @click="fullScreen = false"><v-icon>mdi-window-restore</v-icon></v-btn>
                    <v-btn icon :disabled="processando" @click="fechar"><v-icon>mdi-close</v-icon></v-btn>
                </v-toolbar>

                <tratar-erro-ajax :id="msgDialogId"></tratar-erro-ajax>
                <v-card-text class="pt-5">
                    <v-card outlined class="mb-4">
                        <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">Identificação do paciente</v-card-title>
                        <v-card-text class="pt-3">
                            <v-row dense>
                                <v-col cols="12" md="5"><v-text-field label="Paciente" readonly filled dense hide-details :value="nomePaciente" /></v-col>
                                <v-col cols="12" md="3"><v-text-field label="CPF" readonly filled dense hide-details :value="paciente && paciente.PACIENTE_CPF || '-'" /></v-col>
                                <v-col cols="6" md="2"><v-text-field label="Nascimento" readonly filled dense hide-details :value="dataNascimento" /></v-col>
                                <v-col cols="6" md="2"><v-text-field label="Idade" readonly filled dense hide-details :value="idadePaciente" /></v-col>
                                <v-col cols="12" md="4"><v-text-field label="Sexo" readonly filled dense hide-details :value="descricao(sexos, paciente && paciente.TG_SEXO_ID)" /></v-col>
                                <v-col cols="12" md="4"><v-text-field label="Vulnerabilidade social" readonly filled dense hide-details :value="simNao(paciente && paciente.PACIENTE_VULNERABILIDADE_SOCIAL)" /></v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <v-card outlined class="mb-4">
                        <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">Dados do atendimento e regulação</v-card-title>
                        <v-card-text class="pt-3">
                            <v-row dense>
                                <v-col cols="12" md="3"><v-text-field label="Data/hora de abertura" readonly filled dense hide-details :value="formatarDataHora(chamado.CHAMADO_DATA)" /></v-col>
                                <v-col cols="12" md="3"><v-text-field label="Tipo de chamado" readonly filled dense hide-details :value="descricao(tiposChamado, chamado.TG_CHAMADO_ID)" /></v-col>
                                <v-col cols="12" md="3"><v-text-field label="Prioridade atual" readonly filled dense hide-details :value="descricao(prioridades, chamado.TG_PRIORIDADE_ID)" /></v-col>
                                <v-col cols="12" md="3"><v-text-field label="Horário de atendimento" readonly filled dense hide-details :value="chamado.CHAMADO_HORARIO_ATENDIMENTO || '-'" /></v-col>
                                <v-col cols="12" md="6"><v-text-field label="Unidade de origem" readonly filled dense hide-details :value="unidadeNome('unidadeSolicitante', 'unidade_solicitante')" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Setor de origem" readonly filled dense hide-details :value="chamado.CHAMADO_SETOR_SOLICITANTE || '-'" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Leito de origem" readonly filled dense hide-details :value="chamado.CHAMADO_LEITO_SOLICITANTE || '-'" /></v-col>
                                <v-col cols="12" md="6"><v-text-field label="Unidade de destino" readonly filled dense hide-details :value="unidadeNome('unidadeDestino', 'unidade_destino')" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Setor de destino" readonly filled dense hide-details :value="chamado.CHAMADO_SETOR_DESTINO || '-'" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Leito de destino" readonly filled dense hide-details :value="chamado.CHAMADO_LEITO_DESTINO || '-'" /></v-col>
                                <v-col cols="12" md="6"><v-textarea label="Procedimentos" readonly filled dense rows="2" hide-details :value="procedimentos" /></v-col>
                                <v-col cols="12" md="6"><v-textarea label="Diagnósticos" readonly filled dense rows="2" hide-details :value="diagnosticos" /></v-col>
                                <v-col cols="12" md="8"><v-textarea label="Dispositivos" readonly filled dense rows="1" auto-grow hide-details :value="chamado.CHAMADO_DISPOSITIVOS || '-'" /></v-col>
                                <v-col cols="6" md="2"><v-text-field label="Peso" readonly filled dense hide-details :value="chamado.CHAMADO_PESO ? chamado.CHAMADO_PESO + ' kg' : '-'" /></v-col>
                                <v-col cols="6" md="2"><v-text-field label="Ambulância extra" readonly filled dense hide-details :value="simNao(chamado.CHAMADO_AMBULANCIA_EXTRA)" /></v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>

                    <v-card outlined>
                        <v-card-title class="subtitle-2 font-weight-bold blue-grey lighten-5 py-2">Atualização clínica</v-card-title>
                        <v-card-text class="pt-4">
                            <v-row dense>
                                <v-col cols="12" md="4"><v-text-field label="Prioridade atual" readonly filled dense :value="descricao(prioridades, prioridadeAnteriorId)" /></v-col>
                                <v-col cols="12" md="4"><v-select label="Nova prioridade*" :items="prioridadesDisponiveis" item-text="DESCRICAO" item-value="COLUNA_ID" outlined dense clearable v-model="form.TG_PRIORIDADE_ID" /></v-col>
                                <v-col cols="12" md="4"><v-select label="Precaução*" :items="tiposPrecaucao" item-text="DESCRICAO" item-value="COLUNA_ID" outlined dense v-model="form.TG_TIPO_PRECAUCAO_ID" /></v-col>
                                <v-col cols="12" md="4"><v-select label="Suporte O2*" :items="suportesO2" item-text="DESCRICAO" item-value="COLUNA_ID" outlined dense v-model="form.TG_SUPORTE_O2_ID" /></v-col>
                                <v-col cols="12" md="4"><v-select label="Suporte hemodinâmico*" :items="suportesHemodinamicos" item-text="DESCRICAO" item-value="COLUNA_ID" outlined dense v-model="form.TG_SUPORTE_HEMODINAMICO_ID" /></v-col>
                                <v-col cols="6" md="4"><v-text-field label="Temperatura*" maxlength="20" outlined dense v-model="form.ATUALIZACAO_CLINICA_TEMPERATURA" /></v-col>
                                <v-col cols="6" md="4"><v-text-field label="Pressão arterial*" maxlength="20" outlined dense v-model="form.ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL" /></v-col>
                                <v-col cols="6" md="4"><v-text-field label="Frequência cardíaca*" maxlength="20" outlined dense v-model="form.ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA" /></v-col>
                                <v-col cols="6" md="4"><v-text-field label="Saturação O2*" maxlength="20" outlined dense v-model="form.ATUALIZACAO_CLINICA_SATURACAO_O2" /></v-col>
                                <v-col cols="6" md="4"><v-text-field label="Escala Glasgow" maxlength="20" outlined dense v-model="form.ATUALIZACAO_CLINICA_ESCALA_GLASGOW" /></v-col>
                                <v-col cols="12" md="6"><v-text-field label="Profissional responsável*" maxlength="150" outlined dense v-model="form.ATUALIZACAO_CLINICA_PROFISSIONAL" /></v-col>
                                <v-col cols="6" md="3"><v-select label="Conselho*" :items="['CRM', 'COREN']" outlined dense v-model="form.ATUALIZACAO_CLINICA_CONSELHO" /></v-col>
                                <v-col cols="6" md="3"><v-text-field label="Nº do conselho*" maxlength="6" inputmode="numeric"
                                    hint="Informe exatamente 6 dígitos" persistent-hint outlined dense
                                    :rules="[regraNumeroConselho]" v-model="form.ATUALIZACAO_CLINICA_NUMERO_CONSELHO" /></v-col>
                                <v-col cols="12"><v-textarea label="Justificativa médica*" outlined rows="4" v-model="form.ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA" /></v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-card-text>

                <v-divider></v-divider>
                <v-card-actions>
                    <v-alert v-if="!processando && !form.TG_PRIORIDADE_ID" dense outlined type="info" class="mb-0 py-1">
                        Selecione a nova prioridade para salvar.
                    </v-alert>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" outlined tile :loading="processando" :disabled="!podeSalvar"
                        title="Salvar atualização clínica" @click="salvar">Salvar Atualização</v-btn>
                    <v-btn color="red" outlined tile :disabled="processando" @click="fechar">Fechar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2";
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";

export default {
    name: "AtualizacaoClinica",
    components: { TratarErroAjax },
    data() {
        return {
            msgId: "msgAtualizacaoClinica",
            msgDialogId: "msgAtualizacaoClinicaDialog"
        };
    },
    computed: {
        ...mapGetters({
            chamado: "AtualizacaoClinicaModule/getChamado",
            atualizacoes: "AtualizacaoClinicaModule/getAtualizacoes",
            prioridadeAnteriorId: "AtualizacaoClinicaModule/getPrioridadeAnteriorId",
            podeCriar: "AtualizacaoClinicaModule/getPodeCriar",
            carregando: "AtualizacaoClinicaModule/getCarregando",
            processando: "AtualizacaoClinicaModule/getProcessando",
            form: "AtualizacaoClinicaModule/getForm",
            prioridades: "DominioModule/getPrioridades",
            tiposPrecaucao: "DominioModule/getTiposPrecaucao",
            suportesO2: "DominioModule/getSuportesO2",
            suportesHemodinamicos: "DominioModule/getSuportesHemodinamicos",
            sexos: "DominioModule/getSexos",
            tiposChamado: "DominioModule/getTiposChamado"
        }),
        dialog: {
            get() { return this.$store.getters["AtualizacaoClinicaModule/getDialog"]; },
            set(valor) { this.$store.dispatch("AtualizacaoClinicaModule/setDialog", valor); }
        },
        fullScreen: {
            get() { return this.$store.getters["AtualizacaoClinicaModule/getFullScreen"]; },
            set(valor) { this.$store.dispatch("AtualizacaoClinicaModule/setFullScreen", valor); }
        },
        paciente() { return this.chamado && this.chamado.paciente; },
        nomePaciente() {
            if (this.paciente && this.paciente.PACIENTE_NOME) return this.paciente.PACIENTE_NOME;
            return this.paciente && Number(this.paciente.PACIENTE_VULNERABILIDADE_SOCIAL) === 1
                ? "PACIENTE EM VULNERABILIDADE SOCIAL" : "-";
        },
        dataNascimento() {
            return this.paciente && this.paciente.PACIENTE_DT_NASCIMENTO
                ? moment(this.paciente.PACIENTE_DT_NASCIMENTO).format("DD/MM/YYYY") : "-";
        },
        idadePaciente() {
            if (!this.paciente || !this.paciente.PACIENTE_DT_NASCIMENTO) return "-";
            return `${moment().diff(moment(this.paciente.PACIENTE_DT_NASCIMENTO), "years")} anos`;
        },
        procedimentos() {
            return (this.chamado.procedimentos || []).map(item => item.PROCEDIMENTO_DESCRICAO).filter(Boolean).join(", ") || "-";
        },
        diagnosticos() {
            return (this.chamado.diagnosticos || []).map(item => item.DIAGNOSTICO_DESCRICAO).filter(Boolean).join(", ") || "-";
        },
        atualizacoesOrdenadas() {
            return [...this.atualizacoes].sort((a, b) => Number(b.ATUALIZACAO_CLINICA_ID) - Number(a.ATUALIZACAO_CLINICA_ID));
        },
        prioridadesDisponiveis() {
            return (this.prioridades || []).filter(item => !String(item.DESCRICAO || "").toUpperCase().includes("AZUL"));
        },
        podeSalvar() {
            if (this.processando) return false;
            const obrigatorios = [
                "TG_PRIORIDADE_ID", "TG_TIPO_PRECAUCAO_ID", "TG_SUPORTE_O2_ID",
                "TG_SUPORTE_HEMODINAMICO_ID", "ATUALIZACAO_CLINICA_TEMPERATURA",
                "ATUALIZACAO_CLINICA_PRESSAO_ARTERIAL", "ATUALIZACAO_CLINICA_FREQUENCIA_CARDIACA",
                "ATUALIZACAO_CLINICA_SATURACAO_O2", "ATUALIZACAO_CLINICA_PROFISSIONAL",
                "ATUALIZACAO_CLINICA_CONSELHO", "ATUALIZACAO_CLINICA_NUMERO_CONSELHO",
                "ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA",
            ];
            return obrigatorios.every(campo => this.form[campo] !== undefined
                && this.form[campo] !== null
                && String(this.form[campo]).trim())
                && /^\d{6}$/.test(String(this.form.ATUALIZACAO_CLINICA_NUMERO_CONSELHO || ""));
        },
    },
    watch: {
        "chamado.CHAMADO_ID": {
            immediate: true,
            handler(id) { if (id) this.carregar(); },
        },
    },
    methods: {
        carregar() {
            return this.$store.dispatch("AtualizacaoClinicaModule/carregar", this.msgId);
        },
        abrirNova() {
            this.$store.dispatch("AtualizacaoClinicaModule/abrirNova");
        },
        fechar() {
            this.$store.dispatch("AtualizacaoClinicaModule/fechar");
        },
        salvar() {
            if (!this.podeSalvar) return;
            this.$store.dispatch("AtualizacaoClinicaModule/salvar", this.msgDialogId)
                .then(dados => {
                    if (dados) Swal.fire("Sucesso", dados.msg, "success");
                });
        },
        descricao(lista, id) {
            const item = (lista || []).find(atual => Number(atual.COLUNA_ID) === Number(id));
            return item ? item.DESCRICAO : "-";
        },
        corPrioridade(id) {
            const valor = this.descricao(this.prioridades, id).toUpperCase();
            if (valor.includes("VERMELHO")) return "red darken-3";
            if (valor.includes("LARANJA")) return "orange darken-2";
            if (valor.includes("AMARELO")) return "amber darken-2";
            if (valor.includes("VERDE")) return "green darken-1";
            return "grey";
        },
        unidadeNome(camel, snake) {
            const unidade = this.chamado[camel] || this.chamado[snake];
            return unidade && unidade.UNIDADE_NOME || "-";
        },
        nomeUsuario(item) { return item.usuario && item.usuario.USUARIO_NOME || "-"; },
        conselho(item) { return `${item.ATUALIZACAO_CLINICA_CONSELHO || "-"} ${item.ATUALIZACAO_CLINICA_NUMERO_CONSELHO || "-"}`; },
        simNao(valor) { return Number(valor) === 1 ? "Sim" : "Não"; },
        regraNumeroConselho(valor) {
            return /^\d{6}$/.test(String(valor || "")) || "Informe exatamente 6 dígitos.";
        },
        formatarDataHora(data) { return data ? moment(data).format("DD/MM/YYYY HH:mm:ss") : "-"; },
    },
};
</script>
