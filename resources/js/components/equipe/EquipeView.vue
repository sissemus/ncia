<template>
    <div>
        <v-card>
            <v-toolbar flat class="elevation-1">
                <v-icon color="primary" class="mr-2">mdi-account-group</v-icon>
                <v-toolbar-title>Cadastro de Equipes</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn fab small elevation="2" color="primary" dark v-bind="attrs" v-on="on" @click="novoEquipe">
                            <v-icon>mdi-plus</v-icon>
                        </v-btn>
                    </template>
                    <span>Nova equipe</span>
                </v-tooltip>
            </v-toolbar>
            <tratar-erro-ajax :id="msgId"></tratar-erro-ajax>
            <div :id="msgIdDebug"></div>

            <!-- Filtros -->
            <v-card-text class="pb-0">
                <v-row dense align="center">
                    <v-col cols="12" sm="4" md="2">
                        <v-text-field label="Nº da equipe" prepend-inner-icon="mdi-pound" outlined dense hide-details
                            clearable autocomplete="off" v-model="equipePesquisa.EQUIPE_ID"
                            @keyup.enter="pesquisar" @click:clear="equipePesquisa.EQUIPE_ID = null"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="4" md="2">
                        <v-text-field label="Veículo" prepend-inner-icon="mdi-ambulance" outlined dense hide-details
                            clearable autocomplete="off" v-model="equipePesquisa.VEICULO_IDENTIFICACAO"
                            @keyup.enter="pesquisar" @click:clear="equipePesquisa.VEICULO_IDENTIFICACAO = null"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="4" md="4">
                        <v-text-field label="Profissional" prepend-inner-icon="mdi-account-search" outlined dense hide-details
                            clearable autocomplete="off" placeholder="Nome do integrante"
                            v-model="equipePesquisa.PROFISSIONAL_NOME"
                            @keyup.enter="pesquisar" @click:clear="equipePesquisa.PROFISSIONAL_NOME = null"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" class="d-flex flex-wrap justify-end align-center">
                        <v-btn color="primary" depressed class="mr-2" @click="pesquisar">
                            <v-icon left>mdi-magnify</v-icon> Pesquisar
                        </v-btn>
                        <v-btn outlined color="grey darken-1" class="mr-2" @click="clear">
                            <v-icon left>mdi-eraser</v-icon> Limpar
                        </v-btn>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn outlined color="warning" v-bind="attrs" v-on="on"
                                    :disabled="processandoEquipeId !== null" @click="desativarEquipesTodas">
                                    <v-icon left>mdi-account-cancel</v-icon> Desativar todas
                                </v-btn>
                            </template>
                            <span>Desativa todas as equipes ativas, exceto as que têm chamado em fila ou em atendimento</span>
                        </v-tooltip>
                    </v-col>
                </v-row>
                <v-row dense align="center" class="mt-1">
                    <v-col cols="12" sm="4" md="2">
                        <v-select label="Situação" prepend-inner-icon="mdi-filter-variant" outlined dense hide-details
                            :items="opcoesSituacao" v-model="equipePesquisa.SITUACAO"></v-select>
                    </v-col>
                    <v-col cols="12" sm="4" md="2">
                        <v-select label="Turno" prepend-inner-icon="mdi-weather-sunset" outlined dense hide-details
                            :items="opcoesTurno" v-model="equipePesquisa.EQUIPE_TURNO"></v-select>
                    </v-col>
                    <v-col cols="12" sm="4" md="3">
                        <v-select label="Plantão" prepend-inner-icon="mdi-clock-outline" outlined dense hide-details
                            :items="opcoesPlantao" v-model="equipePesquisa.SITUACAO_PLANTAO"></v-select>
                    </v-col>
                </v-row>
                <div v-if="filtrosAtivos.length" class="mt-2">
                    <span class="caption grey--text text--darken-1 mr-1">Filtros aplicados:</span>
                    <v-chip v-for="filtro in filtrosAtivos" :key="filtro.chave" small class="mr-1 mb-1"
                        color="primary" outlined>
                        {{ filtro.texto }}
                    </v-chip>
                </div>
            </v-card-text>

            <!-- Lista de equipes -->
            <v-card-text>
                <v-card v-for="eqp in equipes" :key="eqp.EQUIPE_ID" outlined class="mb-3 equipe-card"
                    :class="{ 'equipe-desativada': !ativa(eqp) }">
                    <v-row no-gutters align="center" class="px-4 py-3">
                        <!-- Identificação, veículo, turno, período e status -->
                        <v-col cols="12" md="4" class="pr-md-4 mb-2 mb-md-0">
                            <div class="d-flex align-center flex-wrap">
                                <span class="equipe-numero mr-3">Nº {{ eqp.EQUIPE_ID }}</span>
                                <v-chip small dark :color="corTurno(eqp.EQUIPE_TURNO)" class="font-weight-bold mr-2">
                                    <v-icon small left>{{ iconeTurno(eqp.EQUIPE_TURNO) }}</v-icon>
                                    {{ eqp.EQUIPE_TURNO }}
                                </v-chip>
                                <span class="body-2 font-weight-medium">
                                    <v-icon small color="primary" class="mr-1">mdi-ambulance</v-icon>{{ nomeVeiculo(eqp) }}
                                </span>
                            </div>
                            <div class="body-2 grey--text text--darken-2 mt-1">
                                <v-icon small class="mr-1">mdi-clock-outline</v-icon>{{ formatarPeriodo(eqp) }}
                            </div>
                            <div class="mt-1">
                                <v-chip v-for="status in statusEquipe(eqp)" :key="status.texto" x-small label
                                    class="mr-1 mb-1" :color="status.cor" text-color="white">
                                    <v-icon x-small left>{{ status.icone }}</v-icon>
                                    {{ status.texto }}
                                </v-chip>
                            </div>
                        </v-col>

                        <!-- Profissionais -->
                        <v-col cols="12" md="6">
                            <div v-for="prf in (eqp.equipeProfissional || [])" :key="prf.EQUIPE_PROFISSIONAL_ID"
                                class="d-flex align-center profissional">
                                <v-icon small color="grey" class="mr-2">mdi-account</v-icon>
                                <span class="body-2 font-weight-medium mr-2">
                                    {{ prf.profissional ? prf.profissional.PROFISSIONAL_NOME : '-' }}
                                </span>
                                <span class="caption grey--text">
                                    {{ prf.profissional && prf.profissional.tipoProfissional ? prf.profissional.tipoProfissional.DESCRICAO : '' }}
                                </span>
                            </div>
                            <div v-if="!(eqp.equipeProfissional || []).length" class="caption grey--text">
                                Nenhum profissional ativo
                            </div>
                        </v-col>

                        <!-- Ações (somente equipes ativas) -->
                        <v-col cols="12" md="2" class="text-md-right mt-2 mt-md-0">
                            <template v-if="ativa(eqp)">
                                <v-tooltip v-if="podeTrocarPlantao" bottom>
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-btn icon v-bind="attrs" v-on="on" color="primary"
                                            aria-label="Trocar plantão"
                                            :disabled="processandoEquipeId !== null || dialogPlantao || processandoPlantao"
                                            @click="abrirTrocaPlantao(veiculoDaEquipe(eqp), eqp)">
                                            <v-icon>mdi-account-switch</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Trocar plantão</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-btn icon v-bind="attrs" v-on="on" aria-label="Editar equipe"
                                            :disabled="processandoEquipeId !== null || dialogPlantao"
                                            @click="selecionar(eqp)">
                                            <v-icon>mdi-pencil</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Editar equipe</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on, attrs }">
                                        <!-- span: tooltip também aparece com o botão desabilitado -->
                                        <span v-bind="attrs" v-on="on">
                                            <v-btn icon color="error" aria-label="Desativar equipe"
                                                :loading="processandoEquipeId === eqp.EQUIPE_ID"
                                                :disabled="processandoEquipeId !== null || dialogPlantao || temPendencia(eqp)"
                                                @click="desativar(eqp)">
                                                <v-icon>mdi-account-cancel</v-icon>
                                            </v-btn>
                                        </span>
                                    </template>
                                    <span v-if="temPendencia(eqp)">
                                        Possui chamado em fila ou em atendimento.<br>
                                        Troque o plantão ou conclua/cancele o chamado para desativar.
                                    </span>
                                    <span v-else>Desativar equipe</span>
                                </v-tooltip>
                            </template>
                        </v-col>
                    </v-row>
                </v-card>

                <v-alert v-if="!equipes.length" type="info" outlined dense class="mb-0">
                    Nenhuma equipe encontrada{{ filtrosAtivos.length ? ' para o filtro informado' : '' }}.
                </v-alert>
            </v-card-text>

            <v-divider></v-divider>
            <v-card-actions class="flex-column py-3">
                <v-pagination v-show="pagination.total" v-model="pagination.current_page"
                    :length="pagination.last_page" total-visible="10" @input="onPageChange"></v-pagination>
                <v-chip small class="mt-2">
                    {{ pagination.total }} equipe{{ pagination.total !== 1 ? 's' : '' }}
                </v-chip>
            </v-card-actions>
        </v-card>
        <v-dialog v-model="dialogPlantao" max-width="720" persistent>
            <v-card>
                <v-card-title>Trocar plantão — {{ veiculoPlantao && veiculoPlantao.VEICULO_IDENTIFICACAO }}</v-card-title>
                <v-card-text>
                    <p v-if="equipeOrigemPlantao"><strong>Equipe de origem:</strong>
                        Nº {{ equipeOrigemPlantao.EQUIPE_ID }} — {{ formatarData(equipeOrigemPlantao.EQUIPE_DATA) }} — {{ equipeOrigemPlantao.EQUIPE_TURNO }}</p>
                    <p>Selecione a equipe que assumirá os chamados em fila desta ambulância.
                        Os chamados em atendimento continuarão com a equipe anterior até a conclusão.</p>
                    <v-select v-model="equipePlantaoId" :items="equipesPlantao"
                        item-value="EQUIPE_ID" item-text="descricaoPlantao" label="Equipe que assumirá"
                        :loading="carregandoPlantao" :disabled="carregandoPlantao || processandoPlantao"
                        no-data-text="Não há equipe substituta ativa com turno e data compatíveis nesta ambulância.">
                        <template v-slot:item="{ item }">
                            <v-list-item-content>
                                <v-list-item-title>Equipe Nº {{ item.EQUIPE_ID }} — {{ formatarData(item.EQUIPE_DATA) }} — {{ item.EQUIPE_TURNO }}</v-list-item-title>
                                <v-list-item-subtitle class="text-wrap">{{ item.profissionaisPlantao }}</v-list-item-subtitle>
                            </v-list-item-content>
                        </template>
                    </v-select>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text :disabled="processandoPlantao || carregandoPlantao" @click="dialogPlantao = false">Cancelar</v-btn>
                    <v-btn color="primary" :loading="processandoPlantao"
                        :disabled="carregandoPlantao || processandoPlantao || !equipePlantaoId"
                        @click="confirmarTrocaPlantao">Confirmar troca</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <MdNovoEquipe></MdNovoEquipe>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import { mapGetters } from "vuex";
import TratarErroAjax from "../assets/TratarErroAjax";
import MdNovoEquipe from "./MdNovoEquipe";

export default {
    name: "EquipeView",
    components: { MdNovoEquipe, TratarErroAjax },
    props:{
        tiposProfissional: {type: Array, default: () => []},
        profissionais: {type: Array, default: () => []},
        tiposVeiculo: {type: Array, default: () => []},
        podeTrocarPlantao: {type: Boolean, default: false},
    },
    data() {
        return {
            msgId: 'msgEquipeView',
            msgIdDebug: 'msgEquipeViewDebug',

            processandoEquipeId: null,
            dialogPlantao: false,
            carregandoPlantao: false,
            processandoPlantao: false,
            veiculoPlantao: null,
            equipeOrigemPlantao: null,
            equipePlantaoId: null,
            equipesPlantao: [],

            opcoesSituacao: [
                { text: 'Ativas', value: 'ATIVAS' },
                { text: 'Desativadas', value: 'DESATIVADAS' },
                { text: 'Todas', value: 'TODAS' },
            ],
            opcoesTurno: [
                { text: 'Todos', value: null },
                { text: 'SD (07h às 19h)', value: 'SD' },
                { text: 'SN (19h às 07h)', value: 'SN' },
            ],
            opcoesPlantao: [
                { text: 'Todos', value: null },
                { text: 'Em plantão', value: 'VIGENTE' },
                { text: 'Plantão encerrado', value: 'ENCERRADO' },
            ],
            agora: new Date(),
            relogio: null,
        }
    },
    mounted() {
        this.$store.dispatch('DominioModule/setTipoProfissionais', this.tiposProfissional);
        this.$store.dispatch('ProfissionalViewModule/setProfissionais', this.profissionais);

        this.pesquisar();
        // Atualiza o status "Em plantão / encerrado" sem recarregar a página.
        this.relogio = setInterval(() => { this.agora = new Date(); }, 60000);
    },
    beforeDestroy() {
        clearInterval(this.relogio);
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        equipes() {
            return this.$store.getters['EquipeViewModule/getEquipes'] || [];
        },
        pagination: {
            get() { return this.$store.getters['EquipeViewModule/getPagination'] },
            set(newValue) { this.$store.dispatch('EquipeViewModule/setPagination', newValue) }
        },
        equipePesquisa: {
            get() { return this.$store.getters['EquipeViewModule/getEquipePesquisa'] },
            set(newValue) { this.$store.dispatch('EquipeViewModule/setEquipePesquisa', newValue) }
        },
        // Filtros usados na última pesquisa (não os que estão só digitados no formulário)
        filtrosAtivos() {
            const p = this.$store.getters['EquipeViewModule/getFiltrosAplicados'] || {};
            const texto = (lista, valor) => {
                const item = lista.find(opcao => opcao.value === valor);
                return item ? item.text : valor;
            };
            const ativos = [];

            if (p.SITUACAO && p.SITUACAO !== 'ATIVAS') ativos.push({ chave: 'SITUACAO', texto: `Situação: ${texto(this.opcoesSituacao, p.SITUACAO)}` });
            if (p.EQUIPE_ID) ativos.push({ chave: 'EQUIPE_ID', texto: `Equipe Nº ${p.EQUIPE_ID}` });
            if (p.VEICULO_IDENTIFICACAO) ativos.push({ chave: 'VEICULO_IDENTIFICACAO', texto: `Veículo: ${p.VEICULO_IDENTIFICACAO}` });
            if (p.PROFISSIONAL_NOME) ativos.push({ chave: 'PROFISSIONAL_NOME', texto: `Profissional: ${p.PROFISSIONAL_NOME}` });
            if (p.EQUIPE_TURNO) ativos.push({ chave: 'EQUIPE_TURNO', texto: `Turno: ${texto(this.opcoesTurno, p.EQUIPE_TURNO)}` });
            if (p.SITUACAO_PLANTAO) ativos.push({ chave: 'SITUACAO_PLANTAO', texto: texto(this.opcoesPlantao, p.SITUACAO_PLANTAO) });
            return ativos;
        }
    },
    methods: {
        async abrirTrocaPlantao(veiculo, equipeOrigem) {
            if (this.dialogPlantao || this.processandoEquipeId !== null) return;
            this.veiculoPlantao = veiculo;
            this.equipeOrigemPlantao = equipeOrigem;
            this.equipePlantaoId = null;
            this.equipesPlantao = [];
            this.dialogPlantao = true;
            this.carregandoPlantao = true;
            try {
                const { data } = await axios.get(`${this.baseUrl}/equipe/equipes-plantao`, {
                    params: { VEICULO_ID: veiculo.VEICULO_ID, EQUIPE_ORIGEM_ID: equipeOrigem.EQUIPE_ID }
                });
                this.equipesPlantao = data.map(equipe => {
                    const profissionais = (equipe.equipeProfissional || [])
                        .map(vinculo => vinculo.profissional && vinculo.profissional.PROFISSIONAL_NOME)
                        .filter(Boolean).join(', ');
                    return {
                        ...equipe,
                        profissionaisPlantao: profissionais,
                        descricaoPlantao: `Nº ${equipe.EQUIPE_ID} — ${this.formatarData(equipe.EQUIPE_DATA)} — ${equipe.EQUIPE_TURNO} — ${profissionais}`
                    };
                });
            } catch (error) {
                this.dialogPlantao = false;
                Swal.fire('Não foi possível carregar as equipes',
                    this.mensagemErro(error, 'Tente novamente.'), 'error');
            } finally {
                this.carregandoPlantao = false;
            }
        },

        async confirmarTrocaPlantao() {
            if (this.processandoPlantao || this.carregandoPlantao || !this.equipePlantaoId) return;
            this.processandoPlantao = true;
            try {
                const { data } = await axios.post(`${this.baseUrl}/equipe/trocar-plantao`, {
                    VEICULO_ID: this.veiculoPlantao.VEICULO_ID,
                    EQUIPE_ORIGEM_ID: this.equipeOrigemPlantao.EQUIPE_ID,
                    EQUIPE_ID: this.equipePlantaoId
                });
                this.dialogPlantao = false;
                this.search();
                await Swal.fire(data.transferidos ? 'Plantão atualizado' : 'Nenhuma transferência',
                    data.mensagem, data.transferidos ? 'success' : 'info');
            } catch (error) {
                Swal.fire('Não foi possível trocar o plantão',
                    this.mensagemErro(error, 'Tente novamente.'), 'error');
            } finally {
                this.processandoPlantao = false;
            }
        },

        search() {
            return this.$store.dispatch('EquipeViewModule/search', this.msgId);
        },

        onPageChange() {
            this.search();
        },

        // A busca só acontece pelo botão Pesquisar (ou Enter num campo de texto), Limpar e a paginação.
        pesquisar() {
            this.$store.dispatch('EquipeViewModule/aplicarFiltros');
            this.pagination = { ...this.pagination, current_page: 1 };
            this.search();
        },

        clear() {
            this.equipePesquisa = null; // volta ao padrão (Situação: Ativas)
            this.pesquisar();
        },

        ativa(eqp) {
            return Number(eqp.EQUIPE_ATIVO) === 1;
        },

        temPendencia(eqp) {
            return Number(eqp.CHAMADOS_PENDENTES || 0) > 0;
        },

        nomeVeiculo(eqp) {
            return eqp.veiculo ? eqp.veiculo.VEICULO_IDENTIFICACAO : `Veículo ${eqp.VEICULO_ID}`;
        },

        veiculoDaEquipe(eqp) {
            return eqp.veiculo || { VEICULO_ID: eqp.VEICULO_ID, VEICULO_IDENTIFICACAO: this.nomeVeiculo(eqp) };
        },

        corTurno(turno) {
            return turno === 'SN' ? 'indigo' : 'amber darken-2';
        },

        iconeTurno(turno) {
            return turno === 'SN' ? 'mdi-weather-night' : 'mdi-weather-sunny';
        },

        paraData(valor) {
            if (!valor) return null;
            const data = new Date(String(valor).replace(' ', 'T'));
            return isNaN(data.getTime()) ? null : data;
        },

        formatarPeriodo(eqp) {
            const inicio = this.formatarData(eqp.EQUIPE_DATA);
            const fim = this.formatarData(eqp.EQUIPE_DATA_FIM);
            // Mesmo dia: mostra só a hora no fim (25/11/2026 07:00 → 19:00)
            const fimCurto = inicio && fim && inicio.substring(0, 10) === fim.substring(0, 10)
                ? fim.substring(11)
                : fim;
            return `${inicio || '-'} → ${fimCurto || '-'}`;
        },

        statusEquipe(eqp) {
            const lista = [];

            if (!this.ativa(eqp)) {
                lista.push({
                    texto: eqp.UPDATED_AT ? `Desativada em ${this.formatarData(eqp.UPDATED_AT)}` : 'Desativada',
                    cor: 'grey darken-1', icone: 'mdi-account-cancel'
                });
                return lista;
            }

            const inicio = this.paraData(eqp.EQUIPE_DATA);
            const fim = this.paraData(eqp.EQUIPE_DATA_FIM);
            if (fim && this.agora > fim) {
                lista.push({ texto: 'Plantão encerrado', cor: 'blue-grey', icone: 'mdi-clock-check-outline' });
            } else if (inicio && this.agora < inicio) {
                lista.push({ texto: 'Aguardando início', cor: 'blue-grey lighten-1', icone: 'mdi-clock-start' });
            } else {
                lista.push({ texto: 'Em plantão', cor: 'green darken-1', icone: 'mdi-check-circle' });
            }

            if (this.temPendencia(eqp)) {
                const n = Number(eqp.CHAMADOS_PENDENTES);
                lista.push({
                    texto: `${n} chamado${n > 1 ? 's' : ''} em fila/atendimento`,
                    cor: 'orange darken-2', icone: 'mdi-alert'
                });
            }
            return lista;
        },

        novoEquipe() {
            this.$store.dispatch('MdNovoEquipeModule/setShowModal', true)
        },

        selecionar(equipe) {
            this.$store.dispatch('MdNovoEquipeModule/setEquipe', equipe)
            this.$store.dispatch('MdNovoEquipeModule/setShowModal', true)
        },

        // "Desativar equipe" (antiga lixeira): desativação lógica, nunca exclusão física.
        desativar(equipe) {
            if (this.dialogPlantao || this.processandoEquipeId !== null) return;
            if (!equipe || !equipe.EQUIPE_ID) {
                Swal.fire('Erro', 'Não foi possível identificar a equipe selecionada.', 'error');
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Desativar equipe',
                text: `Deseja desativar a equipe Nº ${equipe.EQUIPE_ID} do veículo ${this.nomeVeiculo(equipe)}?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Desativar',
                denyButtonText: 'Cancelar',
            }).then(result => {
                if (!result.isConfirmed) return;

                this.processandoEquipeId = equipe.EQUIPE_ID;
                axios.delete(`${this.baseUrl}/equipe/deletar`, { params: { EQUIPE_ID: equipe.EQUIPE_ID } })
                    .then(response => {
                        return Swal.fire('Equipe desativada', response.data.mensagem || '', 'success')
                            .then(() => this.search());
                    })
                    .catch(error => {
                        Swal.fire('Desativação não permitida',
                            this.mensagemErro(error, 'Não foi possível desativar a equipe. Tente novamente.'),
                            'warning');
                    })
                    .finally(() => {
                        this.processandoEquipeId = null;
                    });
            });
        },

        desativarEquipesTodas() {
            Swal.fire({
                icon: 'warning',
                title: 'Desativar todas',
                text: 'Deseja desativar todas as equipes ativas? As que têm chamado em fila ou em atendimento serão mantidas.',
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Desativar',
                denyButtonText: 'Cancelar',
            }).then(result => {
                if (!result.isConfirmed) return;

                this.processandoEquipeId = 0;

                axios.post(`${this.baseUrl}/equipe/desativarEquipesTodas`)
                    .then(response => {
                        return Swal.fire('Equipes desativadas', response.data.mensagem || '', 'success')
                            .then(() => this.search());
                    })
                    .catch(error => {
                        Swal.fire('Desativação não permitida',
                            this.mensagemErro(error, 'Não foi possível desativar as equipes. Tente novamente.'),
                            'warning');
                    })
                    .finally(() => {
                        this.processandoEquipeId = null;
                    });
            });
        },

        mensagemErro(error, fallback = 'Não foi possível concluir a operação. Tente novamente.') {
            const response = error && error.response;
            const data = response && response.data;
            if (data && data.mensagem) return data.mensagem;
            if (data && data.message) return data.message;
            if (data && data.errors) {
                const campo = Object.keys(data.errors)[0];
                if (campo && data.errors[campo] && data.errors[campo][0]) {
                    return data.errors[campo][0];
                }
            }
            return fallback;
        },

        formatarData(data) {
            if (!data) {
                return '';
            }

            // Aceita "2026-11-22 07:00:00" e "2026-11-22T07:00:00"
            const [dataParte, horaParte] = String(data).replace('T', ' ').split(' ');
            const partes = dataParte.split('-');

            if (partes.length !== 3) {
                return '';
            }

            const dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;

            if (!horaParte) {
                return dataFormatada;
            }

            return `${dataFormatada} ${horaParte.substring(0, 5)}`;
        },
    }
}
</script>

<style scoped>
.equipe-card {
    border-left: 4px solid #546e7a !important;
}

.equipe-desativada {
    border-left-color: #bdbdbd !important;
    background-color: #fafafa;
    opacity: 0.85;
}

.equipe-numero {
    font-size: 1.15rem;
    font-weight: 700;
    color: #263238;
}

.profissional + .profissional {
    margin-top: 2px;
}
</style>
