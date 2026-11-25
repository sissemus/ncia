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
                    <v-col cols="12" sm="4" md="3">
                        <v-text-field label="Nº da equipe" prepend-inner-icon="mdi-pound" outlined dense hide-details
                            clearable autocomplete="off" v-model="equipePesquisa.EQUIPE_ID"
                            @keyup.enter="pesquisar"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="4" md="3">
                        <v-text-field label="Veículo" prepend-inner-icon="mdi-ambulance" outlined dense hide-details
                            clearable autocomplete="off" v-model="filtros.VEICULO_IDENTIFICACAO"
                            @keyup.enter="pesquisar"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="4" md="6" class="d-flex flex-wrap justify-end align-center">
                        <v-btn color="primary" depressed class="mr-2" @click="pesquisar">
                            <v-icon left>mdi-magnify</v-icon> Pesquisar
                        </v-btn>
                        <v-btn outlined color="grey darken-1" class="mr-2" @click="clear">
                            <v-icon left>mdi-eraser</v-icon> Limpar
                        </v-btn>
                        <v-menu offset-y left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn outlined color="warning" v-bind="attrs" v-on="on"
                                    :disabled="processandoEquipeId !== null">
                                    <v-icon left>mdi-account-cancel</v-icon> Desativar equipes
                                    <v-icon right>mdi-menu-down</v-icon>
                                </v-btn>
                            </template>
                            <v-list dense>
                                <v-list-item @click="desativarEquipes">
                                    <v-list-item-icon class="mr-3"><v-icon color="warning">mdi-history</v-icon></v-list-item-icon>
                                    <v-list-item-content>
                                        <v-list-item-title>Desativar antigas</v-list-item-title>
                                        <v-list-item-subtitle>Plantões já encerrados</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-list-item @click="desativarEquipesTodas">
                                    <v-list-item-icon class="mr-3"><v-icon color="error">mdi-alert</v-icon></v-list-item-icon>
                                    <v-list-item-content>
                                        <v-list-item-title>Desativar todas</v-list-item-title>
                                        <v-list-item-subtitle>Inclusive plantões em andamento</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </v-col>
                </v-row>
                <v-row dense align="center" class="mt-1">
                    <v-col cols="12" sm="4" md="2">
                        <v-select label="Turno" prepend-inner-icon="mdi-weather-sunset" outlined dense hide-details
                            :items="opcoesTurno" v-model="filtros.EQUIPE_TURNO" @change="pesquisar"></v-select>
                    </v-col>
                    <v-col cols="12" sm="8" md="4">
                        <v-text-field label="Profissional" prepend-inner-icon="mdi-account-search" outlined dense hide-details
                            clearable autocomplete="off" placeholder="Nome do integrante"
                            v-model="filtros.PROFISSIONAL_NOME" @keyup.enter="pesquisar"
                            @click:clear="filtros.PROFISSIONAL_NOME = null; pesquisar()"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-select label="Situação do plantão" prepend-inner-icon="mdi-list-status" outlined dense hide-details
                            :items="opcoesSituacao" v-model="filtros.SITUACAO_PLANTAO" @change="pesquisar"></v-select>
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-switch v-model="filtros.SOMENTE_COM_EQUIPE" inset dense hide-details class="mt-0"
                            label="Somente veículos com equipe" @change="pesquisar"></v-switch>
                    </v-col>
                </v-row>
                <div v-if="filtrosAtivos.length" class="mt-2">
                    <span class="caption grey--text text--darken-1 mr-1">Filtros:</span>
                    <v-chip v-for="filtro in filtrosAtivos" :key="filtro.chave" small close class="mr-1 mb-1"
                        color="primary" outlined @click:close="removerFiltro(filtro.chave)">
                        {{ filtro.texto }}
                    </v-chip>
                </div>
            </v-card-text>

            <v-card-text>
                <!-- Veículos com equipe -->
                <v-card v-for="veiculo in veiculosComEquipe" :key="veiculo.VEICULO_ID" outlined class="mb-3 veiculo-card">
                    <v-card-title class="py-2 subtitle-1 blue-grey lighten-5">
                        <v-icon color="primary" class="mr-2">mdi-ambulance</v-icon>
                        <span class="font-weight-bold">{{ veiculo.VEICULO_IDENTIFICACAO }}</span>
                        <v-spacer></v-spacer>
                        <span class="caption grey--text text--darken-1">
                            {{ veiculo.equipes.length }} equipe{{ veiculo.equipes.length > 1 ? 's' : '' }}
                        </span>
                    </v-card-title>

                    <div v-for="(eqp, idxEqp) in veiculo.equipes" :key="eqp.EQUIPE_ID">
                        <v-divider v-if="idxEqp > 0"></v-divider>
                        <v-row no-gutters align="center" class="px-4 py-3">
                            <!-- Identificação, turno, período e status -->
                            <v-col cols="12" md="4" class="pr-md-4 mb-2 mb-md-0">
                                <div class="d-flex align-center flex-wrap">
                                    <span class="equipe-numero mr-3">Nº {{ eqp.EQUIPE_ID }}</span>
                                    <v-chip small dark :color="corTurno(eqp.EQUIPE_TURNO)" class="font-weight-bold">
                                        <v-icon small left>{{ iconeTurno(eqp.EQUIPE_TURNO) }}</v-icon>
                                        {{ eqp.EQUIPE_TURNO }}
                                    </v-chip>
                                </div>
                                <div class="body-2 grey--text text--darken-2 mt-1">
                                    <v-icon small class="mr-1">mdi-clock-outline</v-icon>{{ formatarPeriodo(eqp) }}
                                </div>
                                <v-chip x-small label class="mt-1" :color="statusPlantao(eqp).cor" text-color="white">
                                    <v-icon x-small left>{{ statusPlantao(eqp).icone }}</v-icon>
                                    {{ statusPlantao(eqp).texto }}
                                </v-chip>
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

                            <!-- Ações -->
                            <v-col cols="12" md="2" class="text-md-right mt-2 mt-md-0">
                                <v-tooltip v-if="podeTrocarPlantao" bottom>
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-btn icon v-bind="attrs" v-on="on" color="primary"
                                            aria-label="Trocar plantão"
                                            :disabled="processandoEquipeId !== null || dialogPlantao || processandoPlantao"
                                            @click="abrirTrocaPlantao(veiculo, eqp)">
                                            <v-icon>mdi-account-switch</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Trocar plantão</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-btn icon v-bind="attrs" v-on="on" aria-label="Editar equipe"
                                            :loading="processandoEquipeId === eqp.EQUIPE_ID"
                                            :disabled="processandoEquipeId !== null || dialogPlantao"
                                            @click="selecionar(eqp)">
                                            <v-icon>mdi-pencil</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Editar equipe</span>
                                </v-tooltip>
                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-btn icon v-bind="attrs" v-on="on" color="error" aria-label="Remover equipe"
                                            :loading="processandoEquipeId === eqp.EQUIPE_ID"
                                            :disabled="processandoEquipeId !== null || dialogPlantao"
                                            @click="deletar(eqp, veiculo)">
                                            <v-icon>mdi-delete</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Remover equipe</span>
                                </v-tooltip>
                            </v-col>
                        </v-row>
                    </div>
                </v-card>

                <!-- Veículos sem equipe (compacto) -->
                <div v-if="veiculosSemEquipe.length" class="sem-equipe pa-3 rounded">
                    <div class="caption grey--text text--darken-2 mb-2">
                        <v-icon small class="mr-1">mdi-ambulance</v-icon>
                        Veículos sem equipe ativa nesta página
                    </div>
                    <v-chip v-for="veiculo in veiculosSemEquipe" :key="veiculo.VEICULO_ID"
                        small outlined class="mr-1 mb-1" color="grey darken-1">
                        {{ veiculo.VEICULO_IDENTIFICACAO }}
                    </v-chip>
                </div>

                <v-alert v-if="!veiculosComEquipe.length" type="info" outlined dense class="mt-3 mb-0">
                    Nenhuma equipe ativa encontrada{{ filtrosAtivos.length ? ' para o filtro informado' : '' }}.
                </v-alert>
            </v-card-text>

            <v-divider></v-divider>
            <v-card-actions class="flex-column py-3">
                <v-pagination v-show="pagination.total" v-model="pagination.current_page"
                    :length="pagination.last_page" total-visible="10" @input="onPageChange"></v-pagination>
                <v-chip small class="mt-2">
                    {{ pagination.total }} veículo{{ pagination.total !== 1 ? 's' : '' }}
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

            idxEqp: 0,
            processandoEquipeId: null,
            dialogPlantao: false,
            carregandoPlantao: false,
            processandoPlantao: false,
            veiculoPlantao: null,
            equipeOrigemPlantao: null,
            equipePlantaoId: null,
            equipesPlantao: [],

            filtros: {
                VEICULO_IDENTIFICACAO: null,
                EQUIPE_TURNO: null,
                PROFISSIONAL_NOME: null,
                SITUACAO_PLANTAO: null,
                SOMENTE_COM_EQUIPE: true,
            },
            opcoesTurno: [
                { text: 'Todos', value: null },
                { text: 'SD (07h às 19h)', value: 'SD' },
                { text: 'SN (19h às 07h)', value: 'SN' },
            ],
            opcoesSituacao: [
                { text: 'Todas', value: null },
                { text: 'Em plantão', value: 'VIGENTE' },
                { text: 'Encerrado com chamado pendente', value: 'PENDENTE' },
            ],
            agora: new Date(),
        }
    },
    mounted() {
        this.$store.dispatch('DominioModule/setTipoProfissionais',
            this.tiposProfissional
        );

        this.$store.dispatch('ProfissionalViewModule/setProfissionais',
            this.profissionais
        )

        this.pesquisar();
        // Atualiza o status "Em plantão / encerrado" sem precisar recarregar.
        this.relogio = setInterval(() => { this.agora = new Date(); }, 60000);
    },
    beforeDestroy() {
        clearInterval(this.relogio);
    },
    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl'
        }),
        equipes: {
            get() { return this.$store.getters['EquipeViewModule/getEquipes'] },
            set(newValue) { this.$store.dispatch('EquipeViewModule/setEquipes', newValue) }
        },
        pagination: {
            get() { return this.$store.getters['VeiculoViewModule/getPagination']},
            set(newValue) { this.$store.dispatch('VeiculoViewModule/setPagination', newValue) }
        },
        equipePesquisa: {
            get() { return this.$store.getters['EquipeViewModule/getEquipePesquisa'] },
            set(newValue) { this.$store.dispatch('EquipeViewModule/setEquipePesquisa', newValue) }
        },
        veiculoPesquisa: {
            get() { return this.$store.getters['VeiculoViewModule/getVeiculoPesquisa'] },
            set(newValue) { this.$store.dispatch('VeiculoViewModule/setVeiculoPesquisa', newValue) }
        },
        veiculos: {

            get() {
                return this.$store.getters['VeiculoViewModule/getVeiculos']
                    .filter(veiculo =>
                        veiculo.VEICULO_ATIVO == 1
                        // && veiculo.TG_SITUACAO_VEICULO_ID == 1
                    )
            },
            set(newValue) {
                this.$store.dispatch(
                    'VeiculoViewModule/setVeiculoPesquisa',
                    newValue
                )
            }
        },
        veiculosComEquipe() {
            return this.veiculos.filter(veiculo => veiculo.equipes && veiculo.equipes.length);
        },
        veiculosSemEquipe() {
            return this.veiculos.filter(veiculo => !veiculo.equipes || !veiculo.equipes.length);
        },
        filtrosAtivos() {
            const ativos = [];
            const turno = this.opcoesTurno.find(item => item.value === this.filtros.EQUIPE_TURNO);
            const situacao = this.opcoesSituacao.find(item => item.value === this.filtros.SITUACAO_PLANTAO);

            if (this.equipePesquisa.EQUIPE_ID) ativos.push({ chave: 'EQUIPE_ID', texto: `Equipe Nº ${this.equipePesquisa.EQUIPE_ID}` });
            if (this.filtros.VEICULO_IDENTIFICACAO) ativos.push({ chave: 'VEICULO_IDENTIFICACAO', texto: `Veículo: ${this.filtros.VEICULO_IDENTIFICACAO}` });
            if (this.filtros.EQUIPE_TURNO) ativos.push({ chave: 'EQUIPE_TURNO', texto: `Turno: ${turno ? turno.text : this.filtros.EQUIPE_TURNO}` });
            if (this.filtros.PROFISSIONAL_NOME) ativos.push({ chave: 'PROFISSIONAL_NOME', texto: `Profissional: ${this.filtros.PROFISSIONAL_NOME}` });
            if (this.filtros.SITUACAO_PLANTAO) ativos.push({ chave: 'SITUACAO_PLANTAO', texto: situacao ? situacao.text : this.filtros.SITUACAO_PLANTAO });
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
            const texto = valor => (valor === null || valor === undefined ? '' : String(valor).trim()) || null;
            const equipeId = this.equipePesquisa ? texto(this.equipePesquisa.EQUIPE_ID) : null;

            this.$store.dispatch('VeiculoViewModule/setVeiculoPesquisa', {
                ...this.veiculoPesquisa,
                EQUIPE_ID: equipeId,
                VEICULO_IDENTIFICACAO: texto(this.filtros.VEICULO_IDENTIFICACAO),
                EQUIPE_TURNO: this.filtros.EQUIPE_TURNO,
                PROFISSIONAL_NOME: texto(this.filtros.PROFISSIONAL_NOME),
                SITUACAO_PLANTAO: this.filtros.SITUACAO_PLANTAO,
                SOMENTE_COM_EQUIPE: this.filtros.SOMENTE_COM_EQUIPE ? 1 : null,
            });

            this.$store.dispatch('VeiculoViewModule/searchCadastrar', {
                msgId: this.msgId,
                TG_SITUACAO_VEICULO_ID: 1,
                VEICULO_ATIVO: 1
            });
        },

        onPageChange() {
            this.search();
        },

        pesquisar() {
            this.pagination.current_page = 1;
            this.search();
        },

        clear() {
            this.equipePesquisa = { ...this.equipePesquisa, EQUIPE_ID: null };
            this.filtros = {
                VEICULO_IDENTIFICACAO: null,
                EQUIPE_TURNO: null,
                PROFISSIONAL_NOME: null,
                SITUACAO_PLANTAO: null,
                SOMENTE_COM_EQUIPE: true,
            };
            this.veiculoPesquisa = {
                VEICULO_ID: null,
            };
            this.pesquisar();
        },

        removerFiltro(chave) {
            if (chave === 'EQUIPE_ID') {
                this.equipePesquisa = { ...this.equipePesquisa, EQUIPE_ID: null };
            } else {
                this.filtros[chave] = null;
            }
            this.pesquisar();
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

        statusPlantao(eqp) {
            const inicio = this.paraData(eqp.EQUIPE_DATA);
            const fim = this.paraData(eqp.EQUIPE_DATA_FIM);

            if (fim && this.agora > fim) {
                return { texto: 'Plantão encerrado – chamado pendente', cor: 'orange darken-2', icone: 'mdi-alert' };
            }
            if (inicio && this.agora < inicio) {
                return { texto: 'Aguardando início', cor: 'blue-grey', icone: 'mdi-clock-start' };
            }
            return { texto: 'Em plantão', cor: 'green darken-1', icone: 'mdi-check-circle' };
        },

        novoEquipe() {
            this.$store.dispatch('MdNovoEquipeModule/setShowModal', true)
        },

        selecionar(equipe) {
            console.log(equipe)
            this.$store.dispatch('MdNovoEquipeModule/setEquipe', equipe)
            this.$store.dispatch('MdNovoEquipeModule/setShowModal', true)
        },

        deletar(equipe, veiculo) {
            if (this.dialogPlantao || this.processandoEquipeId !== null) return;
            if (!equipe || !equipe.EQUIPE_ID) {
                Swal.fire('Erro', 'Não foi possível identificar a equipe selecionada.', 'error');
                return;
            }

            let params = {
                EQUIPE_ID: equipe.EQUIPE_ID
            }


            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja excluir a equipe Nº ${equipe.EQUIPE_ID} do veículo ${veiculo.VEICULO_IDENTIFICACAO}?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            }).then(result => {
                if (!result.isConfirmed) return;

                this.processandoEquipeId = equipe.EQUIPE_ID;
                axios.delete(`${this.baseUrl}/equipe/deletar`, { params })
                    .then(response => {
                        return Swal.fire(
                            'Excluído com sucesso!',
                            response.data.mensagem || '',
                            'success'
                        ).then(() => this.search());
                    })
                    .catch(error => {
                        Swal.fire(
                            'Exclusão não permitida',
                            this.mensagemErro(error),
                            'warning'
                        );
                    })
                    .finally(() => {
                        this.processandoEquipeId = null;
                    });
            })
        },
        desativarEquipes() {

            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja desativar as equipes anteriores?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            }).then(result => {
                if (!result.isConfirmed) return;

                this.processandoEquipeId = 0;

                axios.post(`${this.baseUrl}/equipe/desativarEquipes`)
                    .then(response => {
                        return Swal.fire(
                            'Equipes anteriores desativadas com sucesso!',
                            response.data.mensagem || '',
                            'success'
                        ).then(() => this.search());
                    })
                    .catch(error => {
                        Swal.fire(
                            'Desativação não permitida',
                            this.mensagemErro(error),
                            'warning'
                        );
                    })
                    .finally(() => {
                        this.processandoEquipeId = null;
                    });
            })
        },
        desativarEquipesTodas() {

            Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja desativar todas as equipes?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: `Cancelar`,
            }).then(result => {
                if (!result.isConfirmed) return;

                this.processandoEquipeId = 0;

                axios.post(`${this.baseUrl}/equipe/desativarEquipesTodas`)
                    .then(response => {
                        return Swal.fire(
                            'Equipes desativadas com sucesso!',
                            response.data.mensagem || '',
                            'success'
                        ).then(() => this.search());
                    })
                    .catch(error => {
                        Swal.fire(
                            'Desativação não permitida',
                            this.mensagemErro(error),
                            'warning'
                        );
                    })
                    .finally(() => {
                        this.processandoEquipeId = null;
                    });
            })
        },

        mensagemErro(error, fallback = 'Não foi possível excluir a equipe. Tente novamente.') {
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

        truncateText(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        },
        formatarData(data) {
            if (!data) {
                return '';
            }

            // Aceita "2026-11-22 07:00:00" e "2026-11-22T07:00:00"
            const [dataParte, horaParte] = data.replace('T', ' ').split(' ');
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
        preencherComProfissional(equipes) {
            if (!equipes || equipes.length === 0) {
                return '';
            }

            let acumulador = [];
            
            for (let equipe of equipes) {
                for (let prf of equipe.equipeProfissional) {
                    // Cria o texto de cada profissional
                    let nomeProfissional = `${prf.profissional.PROFISSIONAL_NOME} (${prf.profissional.tipoProfissional.DESCRICAO})`;
                    acumulador.push(nomeProfissional);
                }
            }
            
            // Une todos os profissionais colocando um <br> entre eles
            return acumulador.join('\n');
        }

        
    }
}
</script>

<style scoped>
.veiculo-card {
    border-left: 4px solid #546e7a !important;
}

.equipe-numero {
    font-size: 1.15rem;
    font-weight: 700;
    color: #263238;
}

.profissional + .profissional {
    margin-top: 2px;
}

.sem-equipe {
    background-color: #fafafa;
    border: 1px dashed #cfd8dc;
}
</style>