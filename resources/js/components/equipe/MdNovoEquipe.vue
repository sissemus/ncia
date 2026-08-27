<template>
    <div>
        <v-row justify="center">
            <v-dialog
                v-model="showModal"
                persistent
                width="800"
                scrollable
                :fullscreen="fullScreen"
            >
                <v-card>
                    <v-toolbar
                        color="primary"
                        elevation="1"
                        class="flex-grow-0"
                        dark
                    >
                        <v-toolbar-title>
                            Equipe
                        </v-toolbar-title>

                        <v-spacer></v-spacer>

                        <v-btn
                            icon
                            @click="fullScreen = true"
                            v-show="fullScreen === false"
                        >
                            <v-icon>
                                mdi-window-maximize
                            </v-icon>
                        </v-btn>

                        <v-btn
                            icon
                            @click="fullScreen = false"
                            v-show="fullScreen === true"
                        >
                            <v-icon>
                                mdi-window-restore
                            </v-icon>
                        </v-btn>

                        <v-btn
                            icon
                            @click="clearFormAndClose"
                        >
                            <v-icon>
                                mdi-close
                            </v-icon>
                        </v-btn>
                    </v-toolbar>

                    <tratar-erro-ajax
                        :id="msgId"
                    ></tratar-erro-ajax>

                    <div :id="msgIdDebug"></div>

                    <v-card-text class="mt-5">
                        <v-row>
                            <v-col cols="8">
                                <v-select
                                    label="Veículo*"
                                    :items="veiculos"
                                    item-value="VEICULO_ID"
                                    item-text="VEICULO_IDENTIFICACAO"
                                    v-model="VEICULO_ID"
                                ></v-select>
                            </v-col>
                            <v-col cols="4">
                                <v-select 
                                    label="Turno*" 
                                    item-value="value" 
                                    item-text="text" 
                                    v-model="EQUIPE_TURNO"
                                    :items="opcoesTurno"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12">
                                <v-select
                                    label="Tipo de profissional*"
                                    :items="tiposProfissional"
                                    item-value="COLUNA_ID"
                                    item-text="DESCRICAO"
                                    v-model="COLUNA_ID"
                                ></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="11">
                                <v-select
                                    label="Profissional*"
                                    :items="profissionalEspecifico"
                                    item-value="value"
                                    item-text="text"
                                    v-model="PROFISSIONAL_ID"
                                ></v-select>
                            </v-col>
                            <v-col cols="1" style="text-align: right;">
                                <v-btn title="Adicionar profissional" fab small elevation="3" color="success" dark @click="novoProfissional">
                                    <v-icon>mdi-plus</v-icon>
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                        <v-simple-table dense v-show="equipeMontada.length" class="mb-0">
                            <template v-slot:default>
                                <thead>
                                    <tr>
                                        <th class="text-left">Id</th>
                                        <th class="text-left">Profissional</th>
                                        <th class="text-left">Tipo de Profissional</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in equipeMontada" :key="equipe['EQUIPE_ID']">
                                        <td>{{ row['PROFISSIONAL_ID'] }}</td>

                                        <td>
                                            {{ row['PROFISSIONAL_NOME'] }}
                                        </td>
                                        <td>
                                            {{ row['PROFISSIONAL_TIPO'] }}
                                        </td>                            

                                        <td>
                                            <v-btn icon @click="deletarProfissional(row)" title="Remover">
                                                <v-icon>mdi-delete</v-icon>
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </template>
                        </v-simple-table>

                    <v-divider class="ma-0"></v-divider>

                    <v-card-actions>
                        <v-spacer></v-spacer>

                        <v-btn
                            color="primary"
                            dark
                            tile
                            @click="salvar"
                        >
                            salvar
                        </v-btn>

                        <v-btn
                            color="red"
                            dark
                            outlined
                            tile
                            @click="clearFormAndClose"
                        >
                            fechar
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import TratarErroAjax from '../assets/TratarErroAjax';
import Swal from 'sweetalert2';

export default {
    name: 'MdNovoEquipe',

    components: {
        TratarErroAjax
    },
    data() {
        return {
            msgId: 'msgMdNovoEquipe',
            msgIdDebug: 'msgMdNovoEquipeDebug',
            ativos: [
                {
                    id: 1,
                    text: 'Sim'
                },
                {
                    id: 0,
                    text: 'Não'
                }
            ],

            opcoesTurno: [
                { 
                    text: 'SD', 
                    value: 'SD' 
                },
                { 
                    text: 'SN', 
                    value: 'SN' 
                }
            ],
            equipeMontada:[],
            VEICULO_ID: null,
            EQUIPE_TURNO: null,
            PROFISSIONAL_ID: null,
            TABELA_ID: 7,
            COLUNA_ID: null,
            // tiposProfissional:[],
        }
    },
    
    mounted() {
        this.$store.dispatch(
            'VeiculoViewModule/search',
            {
                msgId: this.msgId,
                VEICULO_ATIVO: 1
            }
        );

        this.$store.dispatch(
            'ProfissionalViewModule/search',
            {
                msgId: this.msgId,
                PROFISSIONAL_ATIVO: 1
            }
            
        );

    },

    computed: {
        ...mapGetters({
            baseUrl: 'getBaseUrl',
            tiposProfissional: 'getTipoProfissionais' // Vincula o getter ao nome usado no template
        }),
        showModal: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getShowModal'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setShowModal',
                    valor
                );
            }
        },

        fullScreen: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getFullScreen'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setFullScreen',
                    valor
                );
            }
        },

        equipe: {
            get() {
                return this.$store.getters[
                    'MdNovoEquipeModule/getEquipe'
                ];
            },

            set(valor) {
                this.$store.dispatch(
                    'MdNovoEquipeModule/setEquipe',
                    valor
                );
            }
        },

        veiculos() {
            return this.$store.getters['VeiculoViewModule/getVeiculos']
                .filter(veiculo =>
                    veiculo.TG_SITUACAO_VEICULO_ID == 1 &&
                    veiculo.VEICULO_ATIVO == 1
                )
        },

        profissionais() {
            return this.$store.getters[
                'ProfissionalViewModule/getProfissionais']
                .filter(profissional => 
                profissional.PROFISSIONAL_ATIVO == 1 &&
                profissional.COLUNA_ID == this.COLUNA_ID
            );
        },
        // tiposProfissional(){
        //     return this.$store.getters[
        //         'DominioModule/getTipoProfissionais']
        //         .filter(tipoProfissional => 
        //         tipoProfissional.TABELA_ID == 7 &&
        //         tipoProfissional.ATIVO == 1
        //     );
        // },
        dataFormatada: {
            get() {
                return this.formatarDataParaInput(
                    this.equipe &&
                    this.equipe.EQUIPE_DATA
                );
            },

            set(valor) {
                this.atualizarData(
                    'EQUIPE_DATA',
                    valor
                );
            }
        },
        profissionalEspecifico() {
            // Verifica se a lista existe e não está vazia
            if (!this.profissionais || !Array.isArray(this.profissionais)) {
                return [];
            }

            // Transforma a lista antiga no formato que o Vuetify precisa
            return this.profissionais.map(item => {
                // Monta o texto unindo Nome + Descrição (se a descrição existir)
                const descricao = item.tipoProfissional ? ` (${item.tipoProfissional.DESCRICAO})` : '';
                
                return {
                    value: item.PROFISSIONAL_ID,
                    text: `${item.PROFISSIONAL_NOME}${descricao}`
                };
            });
        },
    },

    methods: {
        clearFormAndClose() {

            this.resetarFormulario();

            this.showModal = false;
        },
        salvar() {
            this.$store.dispatch(
                'TratarErroAjaxModule/fecharAlert',
                this.msgId
            );

            // if (!this.equipe) {
            //     return;
            // }

            if (!this.equipeMontada) {
                return;
            }

            // const equipe = this.equipe

            const equipeMontada = this.equipeMontada

            const dados = {
                ...equipeMontada
                
            };

            const estaEditando =
                dados.EQUIPE_ID !== null &&
                dados.EQUIPE_ID !== undefined;

            axios({
                method: estaEditando
                    ? 'PUT'
                    : 'POST',

                url: estaEditando
                    ? `${this.baseUrl}/equipe/alterar`
                    : `${this.baseUrl}/equipe/inserir`,

                data: dados
            })
            .then(() => {
                this.clearFormAndClose();

                Swal.fire(
                    'Sucesso',
                    'Salvo com sucesso',
                    'success'
                ).then(() => {
                    this.$store.dispatch(
                        'EquipeViewModule/search',
                        this.msgId
                    );
                    this.$store.dispatch(
                        'VeiculoViewModule/search',
                        this.msgId
                    );
                });
            })
            .catch(error => {
                console.error(
                    'ERRO: ',
                    error
                );

                this.$store.dispatch(
                    'TratarErroAjaxModule/tratarErro',
                    {
                        id: this.msgId,
                        response: error.response
                    }
                );
            });
        },

        formatarDataParaInput(valor) {
            if (!valor) {
                return '';
            }

            /*
             * Caso o Laravel retorne:
             *
             * 2026-08-12 00:00:00.000
             */
            if (
                /^\d{4}-\d{2}-\d{2} /.test(valor)
            ) {
                return valor.substring(0, 10);
            }

            /*
             * Caso venha no formato:
             *
             * 12-08-2026
             */
            if (
                /^\d{2}-\d{2}-\d{4}/.test(valor)
            ) {
                const partes = valor
                    .substring(0, 10)
                    .split('-');

                return [
                    partes[2],
                    partes[1],
                    partes[0]
                ].join('-');
            }

            /*
             * Caso o Laravel retorne ISO:
             *
             * 2026-08-12T03:00:00.000000Z
             */
            const iso = valor.replace(
                /\.(\d{3})\d+Z$/,
                '.$1Z'
            );

            const data = new Date(iso);

            if (isNaN(data.getTime())) {
                return '';
            }

            const partes = new Intl.DateTimeFormat(
                'en-CA',
                {
                    timeZone: 'America/Fortaleza',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                }
            ).formatToParts(data);

            const ano = partes.find(
                item => item.type === 'year'
            ).value;

            const mes = partes.find(
                item => item.type === 'month'
            ).value;

            const dia = partes.find(
                item => item.type === 'day'
            ).value;

            return `${ano}-${mes}-${dia}`;
        },

        atualizarData(campo, data) {
            if (!this.equipe || !data) {
                return;
            }

            const horaAtual =
                this.formatarHoraParaInput(
                    this.equipe[campo]
                ) || '00:00';

            /*
             * Atualiza diretamente o campo original.
             *
             * Não faça:
             *
             * this.dataFormatada = valor;
             *
             * pois isso chama o setter novamente.
             */
            this.equipe[campo] =
                `${data} ${horaAtual}:00`;
        },
        novoProfissional() {
            
            const id = this.PROFISSIONAL_ID;
            // se o id estiver null
            if (id == null)
                return '' 

            // informações desse profissional selecionado
            const profissional = this.profissionais.find(
                item => item.PROFISSIONAL_ID === id
            );

            //verifica se esse profissional já foi adicionado a lista de profissionais selecionados
            const profissionalExiste = this.equipeMontada.find(
                item => item.PROFISSIONAL_ID === id
            )

            if(profissionalExiste){
                console.log('Profissional já adicionado a lista')
                return
            }

            //adiciona à lista, o profissional e suas características
            this.equipeMontada.push(
                {
                    VEICULO_ID: this.VEICULO_ID,
                    EQUIPE_DATA: null, //pegar a data do servidor
                    EQUIPE_TURNO: this.EQUIPE_TURNO,
                    EQUIPE_ATIVO: 1,
                    PROFISSIONAL_ID: this.PROFISSIONAL_ID, //para facilitar a exclusão
                    PROFISSIONAL_NOME: profissional.PROFISSIONAL_NOME, //para facilitar a exclusão
                    PROFISSIONAL_TIPO: profissional.tipoProfissional.DESCRICAO, //para facilitar a exclusão
                }
            );
            
        },
        async deletarProfissional(profissional) {

            let id = profissional.PROFISSIONAL_ID

            const result = await Swal.fire({
                icon: 'warning',
                title: 'Alerta',
                text: `Deseja excluir o profissional ${profissional.PROFISSIONAL_NOME} ?`,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Confirmar',
                denyButtonText: 'Cancelar',
            })

            // Só remove se o usuário clicou em Confirmar
            if (result.isConfirmed) {
                const index = this.equipeMontada.findIndex(
                    item => item.PROFISSIONAL_ID == id
                )

                if (index !== -1) {
                    this.equipeMontada.splice(index, 1)
                }
            }
        },
        resetarFormulario() {

            this.equipeMontada = [];

            this.VEICULO_ID = null;
            this.EQUIPE_TURNO = null;
            this.PROFISSIONAL_ID = null;

            this.equipe = null;

            this.fullScreen = false;

            this.$store.dispatch(
                'TratarErroAjaxModule/fecharAlert',
                this.msgId
            );
        },
    }
}
</script>

<style scoped>
input[type='date'],
input[type='time'] {
    width: 100%;
    min-height: 40px;
    padding: 8px;
    border: 1px solid #bdbdbd;
    border-radius: 4px;
    font-size: 14px;
}

label {
    display: block;
    margin-bottom: 6px;
    color: #555;
    font-size: 14px;
}
</style>
