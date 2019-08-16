<template>
    <div @focusout="closeLists">
        <!-- Modal Add Order-->
        <div class="modal fade" id="addAtividadeModal" tabindex="-1" role="dialog" aria-labelledby="addAtividadeModal"
             data-keyboard="false" data-backdrop="static" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="container box" @click="closeLists">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addAtividadeModal">{{getTitle}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoading">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <br>

                        <v-stepper v-model="stepper">
                            <v-stepper-header>
                                <v-stepper-step
                                    :complete="atividade.titulo && atividade.titulo.length <= 255 && atividade.descricao &&
                                                atividade.descricao.length >=25 && atividade.visibilidade && visibilidades.includes(atividade.visibilidade) &&
                                                tipoSelected && (tipoSelected != 'outro' || outroTipo && outroTipo.length <= 255)"
                                    editable
                                    step="1"
                                    :rules="[() => stepper == 1 || (stepper > 1 && atividade.titulo && atividade.titulo.length <= 255 && atividade.descricao &&
                                                atividade.descricao.length >=25 && atividade.visibilidade && visibilidades.includes(atividade.visibilidade) &&
                                                tipoSelected && (tipoSelected != 'outro' || outroTipo && outroTipo.length <= 255))]"
                                >
                                    Descrição
                                </v-stepper-step>

                                <v-divider></v-divider>

                                <v-stepper-step
                                    :complete="atividade.patrimonios && atividade.patrimonios.length > 0"
                                    editable
                                    step="2"
                                    :rules="[() => !(atividade.patrimonios && atividade.patrimonios.length == 0 && stepper > 2)]"
                                >Escolher patrimonio(s)
                                </v-stepper-step>

                                <v-divider></v-divider>

                                <v-stepper-step
                                    :complete="atividade.participantes && atividade.participantes.length > 0 &&
                                             (!temGrupo || (temGrupo && numeroElementos > 1 && numeroElementos < 7)) &&
                                             (!chatExist || chatExist && chatAssunto.length <= 100)"
                                    editable
                                    step="3"
                                    :rules="[() => stepper < 4 || (atividade.participantes && atividade.participantes.length > 0 && stepper == 4 &&
                                             (!temGrupo || (temGrupo && numeroElementos > 1 && numeroElementos < 7)) &&
                                             (!chatExist || chatExist && chatAssunto.length <= 100))]"
                                >
                                    Selecionar participante(s)
                                </v-stepper-step>

                                <v-divider></v-divider>

                                <v-stepper-step
                                    :complete="this.dataInicio && !(duracao == 2 && !this.dataFinal)"
                                    editable
                                    step="4"
                                    :rules="[() => stepper < 4 || (this.dataInicio && !(this.duracao == 2
                                                    && !this.dataFinal))]"
                                >
                                    Definir data
                                </v-stepper-step>
                            </v-stepper-header>
                            <v-stepper-items>
                                <v-stepper-content step="1">
                                    <div class="form-group">
                                            <v-text-field id="inputTitulo"
                                                          v-model="atividade.titulo"
                                                          label="Título"
                                                          :rules="[v => !!v || 'Titulo é obrigatório',
                                                            v => v && v.length <= 255 || 'máximo 255 caracteres']"
                                                          counter="255"
                                                          required
                                                          clearable
                                            >
                                                <template v-slot:append v-if="!atividade.titulo">
                                                    <v-tooltip left>
                                                        <template v-slot:activator="{ on }">
                                                            <v-icon v-on="on">help</v-icon>
                                                        </template>
                                                        Nome da atividade
                                                    </v-tooltip>
                                                </template>
                                            </v-text-field>
                                    </div>
                                    <div class="form-group">
                                        <v-textarea
                                            id="inputDescricao"
                                            v-model="atividade.descricao"
                                            label="Descrição"
                                            :rules="[v => !!v || 'Descrição é obrigatória',
                                             v => v && v.length >= 25 || 'minimo 25 caracteres']"
                                            counter="1000"
                                            required
                                        >
                                            <template v-slot:append v-if="!atividade.descricao">
                                            <v-tooltip left>
                                                <template v-slot:activator="{ on }">
                                                    <v-icon v-on="on">help</v-icon>
                                                </template>
                                                Descrição dos objetivos e informações relevantes sobre a atividade
                                            </v-tooltip>
                                            </template>
                                        </v-textarea>
                                    </div>
                                    <br>
                                    <v-layout>
                                        <v-flex>
                                            <div @click="setOpenList('tipo')">
                                                <v-select
                                                    label="Tipo"
                                                    v-model="tipoSelected"
                                                    :items="tipos"
                                                    :rules="[v => !!v || 'Tipo de atividade é obrigatória',
                                                        v => !!tipos.includes(v) || 'valor inválido']"
                                                    class="input-group--focused"
                                                    required
                                                    clearable
                                                    ref="selectTipo"
                                                >
                                                    <template v-slot:append v-if="!tipoSelected">
                                                        <v-tooltip left>
                                                            <template v-slot:activator="{ on }">
                                                                <v-icon v-on="on">help</v-icon>
                                                            </template>
                                                            Formato da atividade
                                                        </v-tooltip>
                                                    </template>
                                                </v-select>
                                            </div>
                                        </v-flex>
                                        <v-spacer v-if="tipoSelected === 'outro'"></v-spacer>
                                        <v-flex v-if="tipoSelected === 'outro'">
                                            <div class="form-group">
                                                <v-text-field id="inputTipoOutro"
                                                              v-model="outroTipo"
                                                              label="Indique o tipo da atividade"
                                                              :rules="[v => !!v || 'Tipo é obrigatório',
                                                                v => v && v.length <= 255 || 'máximo 255 caracteres']"
                                                              counter="255"
                                                              clearable
                                                              required
                                                ></v-text-field>
                                            </div>
                                        </v-flex>
                                    </v-layout>
                                    <br>
                                    <div @click="setOpenList('visibilidade')">
                                        <v-select
                                            label="Visibilidade"
                                            v-model="atividade.visibilidade"
                                            :items="visibilidades"
                                            :rules="[v => !!v || 'Visibilidade é obrigatória',
                                                v => !!visibilidades.includes(v) || 'valor inválido']"
                                            class="input-group--focused"
                                            required
                                            clearable
                                            ref="selectVisibilidade"
                                        >
                                            <template v-slot:append v-if="!atividade.visibilidade">
                                                <v-tooltip left>
                                                    <template v-slot:activator="{ on }">
                                                        <v-icon v-on="on">help</v-icon>
                                                    </template>
                                                    <span>Restringir a visualização desta atividade perante os utilizadores:</span>
                                                    <br>
                                                    <span>Apenas os utilizadores inseridos na categoria escolhida poderão ver a atividade</span>
                                                </v-tooltip>
                                            </template>
                                        </v-select>
                                    </div>
                                    <br>
                                    <v-layout>
                                        <v-spacer></v-spacer>
                                        <v-tooltip left>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon flat v-on="on" color="primary" @click="stepper = 2">
                                                    <v-icon>fa fa-angle-right</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Separador Seguinte</span>
                                        </v-tooltip>
                                        <v-btn color="teal lighten-5" @click="stepper=4">fim</v-btn>
                                    </v-layout>
                                </v-stepper-content>


                                <v-stepper-content step="2">
                                    <div @click="setOpenList('patrimonios')">
                                        <v-combobox
                                            v-model="atividade.patrimonios"
                                            :items="patrimonios"
                                            item-text="nome"
                                            label="Patrimonios"
                                            multiple
                                            chips
                                            class="input-group--focused"
                                            deletable-chips
                                            ref="selectPatrimonios"
                                            :rules="[v => v.length != 0 || 'Património é obrigatório']"
                                            autofocus
                                            hide-no-data
                                            required
                                            @input="atividade.patrimonios = validarInputComboBox(atividade.patrimonios)"
                                        >
                                            <template v-slot:append v-if="atividade.patrimonios && atividade.patrimonios.length == 0">
                                                <v-tooltip left>
                                                    <template v-slot:activator="{ on }">
                                                        <v-icon v-on="on">help</v-icon>
                                                    </template>
                                                    Escolher patrimónios relacionados com a atividade
                                                </v-tooltip>
                                            </template>
                                        </v-combobox>
                                    </div>
                                    <br>
                                    <v-layout>
                                        <v-tooltip right>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon flat v-on="on" color="primary" @click="stepper = 1">
                                                    <v-icon>fa fa-angle-left</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Separador Anterior</span>
                                        </v-tooltip>
                                        <v-spacer></v-spacer>
                                        <v-tooltip left>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon flat v-on="on" color="primary" @click="stepper = 3">
                                                    <v-icon>fa fa-angle-right</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Separador Seguinte</span>
                                        </v-tooltip>

                                        <v-btn color="teal lighten-5" @click="stepper=4">fim</v-btn>
                                    </v-layout>
                                </v-stepper-content>


                                <v-stepper-content step="3">
                                    <div @click="setOpenList('alunos')">
                                        <v-combobox
                                            v-if="turmas.length > 0"
                                            v-model="turmasSelecionadas"
                                            :items="turmas"
                                            item-text="nome"
                                            label="Turmas"
                                            multiple
                                            chips
                                            class="input-group--focused"
                                            deletable-chips
                                            autofocus
                                            hide-no-data
                                            @input="turmasSelecionadas = validarInputComboBox(turmasSelecionadas)"
                                        >
                                            <template v-slot:append v-if="!turmasSelecionadas || turmasSelecionadas.length == 0">
                                                <v-tooltip left>
                                                    <template v-slot:activator="{ on }">
                                                        <v-icon v-on="on">help</v-icon>
                                                    </template>
                                                    Selecionar turmas que participarão na atividade
                                                </v-tooltip>
                                            </template>
                                        </v-combobox>
                                        <v-combobox
                                            v-model="atividade.participantes"
                                            :items="alunos"
                                            item-text="nome"
                                            item-value=item
                                            label="Alunos"
                                            multiple
                                            chips
                                            class="input-group--focused"
                                            deletable-chips
                                            ref="selectAlunos"
                                            autofocus
                                            hide-no-data
                                            @input="atividade.participantes = validarInputComboBox(atividade.participantes)"
                                        >
                                            <template v-slot:append v-if="!atividade.participantes || atividade.participantes.length == 0">
                                                <v-tooltip left>
                                                    <template v-slot:activator="{ on }">
                                                        <v-icon v-on="on">help</v-icon>
                                                    </template>
                                                    Selecionar alunos que participarão na atividade
                                                </v-tooltip>
                                            </template>
                                            <template v-slot:selection="{ item, index }">
                                                <v-chip v-if="index <= 4">
                                                    <span>{{ getPrimeiroUltimoNome(item.nome) }}</span>
                                                </v-chip>
                                                <span
                                                    v-if="index === 5"
                                                    class="grey--text caption"
                                                >(+{{ atividade.participantes.length - 5 }} outros)</span>
                                            </template>
                                        </v-combobox>
                                    </div>
                                    <br>
                                    <div class="form-group">
                                        <v-layout>
                                            <v-radio-group class="black--text" label="Formato de Trabalho:"
                                                           v-model="temGrupo" row>
                                                <v-radio color="primary" label="Individual" :value="false"></v-radio>
                                                <v-radio color="primary" label="Grupo" :value="true"></v-radio>
                                            </v-radio-group>
                                            <v-text-field
                                                v-if="temGrupo"
                                                type="number"
                                                min="2"
                                                max="6"
                                                step="1"
                                                id="inputNumeroElementos"
                                                v-model="numeroElementos"
                                                label="Numero de elementos por grupo"
                                                :rules="[v => !!v && Number.isInteger(Number(v)) || 'Numero de elementos é obrigatório',
                                                 v => v > 1 || 'O minimo de elementos é 1',
                                                 v => v < 7 || 'O maximo de elementos é 6']"
                                                required
                                            >
                                                <template v-slot:append v-if="!numeroElementos">
                                                    <v-tooltip left>
                                                        <template v-slot:activator="{ on }">
                                                            <v-icon v-on="on">help</v-icon>
                                                        </template>
                                                        Numero maximo de alunos por grupo
                                                    </v-tooltip>
                                                </template>
                                            </v-text-field>
                                        </v-layout>
                                    </div>
                                    <v-spacer></v-spacer>
                                    <div class="form-group">
                                        <v-layout>
                                            <v-switch
                                                v-model="chatExist"
                                                :label="chatExist ? 'Chat Ativo': 'Chat Desativado'"
                                                color="primary"
                                            >
                                                <template v-slot:append>
                                                    <v-tooltip right v-if="!chatExist">
                                                        <template v-slot:activator="{ on }">
                                                            <v-icon v-on="on">help</v-icon>
                                                        </template>
                                                        Chat onde os participantes poderão comunicar durante a atividade
                                                    </v-tooltip>
                                                </template>
                                            </v-switch>
                                            <v-text-field
                                                v-if="chatExist"
                                                id="inputChatAssunto"
                                                v-model="chatAssunto"
                                                label="Assunto"
                                                :rules="[v => v && v.length <= 100 || 'máximo 255 caracteres']"
                                                counter="100"
                                                clearable
                                            >
                                                <template v-slot:append v-if="!chatAssunto">
                                                    <v-tooltip left>
                                                        <template v-slot:activator="{ on }">
                                                            <v-icon v-on="on">help</v-icon>
                                                        </template>
                                                        Titulo/Assunto do chat
                                                    </v-tooltip>
                                                </template>
                                            </v-text-field>
                                        </v-layout>
                                    </div>
                                    <v-spacer></v-spacer>
                                    <v-layout>
                                        <v-tooltip right>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon flat v-on="on" color="primary" @click="stepper = 2">
                                                    <v-icon>fa fa-angle-left</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Separador Anterior</span>
                                        </v-tooltip>
                                        <v-spacer></v-spacer>
                                        <v-tooltip left>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon flat v-on="on" color="primary" @click="stepper = 4">
                                                    <v-icon>fa fa-angle-right</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Separador Seguinte</span>
                                        </v-tooltip>
                                    </v-layout>
                                </v-stepper-content>

                                <v-stepper-content step="4">
                                    <p>Duração:</p>
                                    <v-radio-group v-model="duracao" row>
                                        <v-radio color="primary" label="1 dia" :value="1"></v-radio>
                                        <v-radio color="primary" label="Intervalo de dias" :value="2"></v-radio>
                                    </v-radio-group>
                                    <br>
                                    <v-layout>
                                        <v-flex xs12 sm5>
                                            <v-menu
                                                v-model="menu1"
                                                :close-on-content-click="false"
                                                :nudge-right="40"
                                                lazy
                                                transition="scale-transition"
                                                offset-y
                                                full-width
                                                min-width="290px"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-text-field
                                                        v-model="dataInicio"
                                                        :label="duracao == 1 ? 'Data' : 'Data Inicial'"
                                                        prepend-icon="event"
                                                        readonly
                                                        required
                                                        v-on="on"
                                                    ></v-text-field>
                                                </template>
                                                <v-date-picker :min="new Date().toISOString().substr(0, 10)"
                                                               v-model="dataInicio" @input="menu1 = false"
                                                               clearable required></v-date-picker>
                                            </v-menu>
                                        </v-flex>
                                        <v-spacer></v-spacer>
                                        <v-flex xs12 sm5 v-if="duracao == 2">
                                            <v-menu
                                                v-model="menu2"
                                                :close-on-content-click="false"
                                                :nudge-right="40"
                                                lazy
                                                transition="scale-transition"
                                                offset-y
                                                full-width
                                                min-width="290px"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-text-field
                                                        v-model="dataFinal"
                                                        label="Data Final"
                                                        prepend-icon="event"
                                                        readonly
                                                        v-on="on"
                                                    ></v-text-field>
                                                </template>
                                                <v-date-picker :min="minDataFinal" v-model="dataFinal" clearable
                                                               @input="menu2 = false" required></v-date-picker>
                                            </v-menu>
                                        </v-flex>
                                    </v-layout>
                                    <br>
                                    <v-layout>
                                        <v-tooltip right>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon flat v-on="on" color="primary" @click="stepper = 3">
                                                    <v-icon>fa fa-angle-left</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Separador Anterior</span>
                                        </v-tooltip>
                                        <v-spacer></v-spacer>
                                        <div v-if="!isLoading">
                                            <v-btn color="red" class="btn white--text" v-on:click.prevent="cancel">
                                                cancelar
                                            </v-btn>
                                            <v-btn v-if="!atividade.id" color="primary" class="btn"
                                                   v-on:click.prevent="save"
                                                   :disabled="hasErrors">
                                                Registar
                                            </v-btn>
                                            <v-btn v-else color="primary" class="btn" v-on:click.prevent="edit"
                                                   :disabled="hasErrors">
                                                Guardar
                                            </v-btn>

                                        </div>
                                        <div v-else>
                                            <loader color="green" size="32px"></loader>
                                        </div>
                                    </v-layout>
                                </v-stepper-content>
                            </v-stepper-items>
                        </v-stepper>

                        <br>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        props: ['atividade', 'alunos', 'turmas'],
        created() {
            //this.getTipos();
            if (this.$store.state.user.tipo === 'professor') {
                this.getPatrimonios();
            }
        },
        data: function () {
            return {
                tipos: ['visita de estudo', 'trabalho em familia', 'trabalho de pesquisa', 'definir tipos de patrimonio', 'outro'],
                tipoSelected: '',
                outroTipo: '',
                visibilidades: ['privado', 'visivel para a escola', 'publico'],
                patrimonios: [],
                turmasSelecionadas: [],
                selTipoAberto: false,
                selVisibilidadeAberto: false,
                selAlunosAberto: false,
                selPatrimoniosAbertos: false,
                chatExist: false,
                chatAssunto: '',
                temGrupo: false,
                numeroElementos: 2,
                isLoading: false,
                stepper: 1,

                duracao: 1,
                dataInicio: null,
                dataFinal: null,
                menu1: false,
                menu2: false,
            };
        },
        methods: {
            isCreated() {
                return (this.atividade.id && this.atividade.id) > 0 ? false : true;
            },
            save: function () {
                this.isLoading = true;
                this.prepararAtividade();
                axios.post('/api/atividades', this.atividade).then(response => {
                    this.toastPopUp("success", "Atividade Criada!");
                    this.$socket.emit('multiplos_atualizar_notificacoes', this.atividade.participantes);
                    this.$emit('atualizar');
                    $('#addAtividadeModal').modal('hide');
                    this.isLoading = false;
                    this.cleanForm();
                }).catch(error => {
                    this.isLoading = false;
                    this.toastErrorApi(error);
                })
            },
            edit: function () {
                this.isLoading = true;
                this.prepararAtividade();
                axios.put('/api/atividades/' + this.atividade.id, this.atividade).then(response => {
                    this.toastPopUp("success", "Atividade Atualizada!");
                    this.$socket.emit('multiplos_atualizar_notificacoes', this.atividade.participantes);
                    this.$emit('atualizar');
                    if (this.tipoSelected === 'outro') {
                        this.$emit('atualizarTipos');
                    }
                    $('#addAtividadeModal').modal('hide');
                    this.isLoading = false;
                    this.cleanForm();
                }).catch(error => {
                    this.isLoading = false;
                    this.toastErrorApi(error);this.toastErrorApi(error);
                })
            },
            prepararAtividade() {
                this.atividade.chatExist = this.chatExist;
                this.atividade.chat = {assunto: this.chatAssunto};
                this.atividade.tipo = this.tipoSelected === 'outro' ? this.outroTipo : this.tipoSelected;
                this.atividade.numeroElementos = this.temGrupo ? this.numeroElementos : 1;
                this.atividade.dataInicio = this.dataInicio;
                if (this.duracao == 2) {
                    this.atividade.dataFinal = this.dataFinal;
                } else {
                    this.atividade.dataFinal = null;
                }
            },
            cancel: function () {
                $('#addAtividadeModal').modal('hide');
                this.cleanForm();
            },
            cleanForm: function () {
                this.atividade.id = null;
                this.atividade.titulo = "";
                this.atividade.descricao = "";
                this.atividade.numeroElementos = 2;
                this.atividade.visibilidade = "";
                this.atividade.participantes = [];
                this.atividade.patrimonios = [];
                this.atividade.chat = null;
                this.atividade.tipo = null;
                this.tipoSelected = '';
                this.outroTipo = '';
                this.chatAssunto = "";
                this.chatExist = false;
                this.duracao = 1;
                this.stepper = 1;
                this.dataInicio = null;
                this.dataFinal = null;
                this.closeLists();
            },
            getPatrimonios(url = '/api/patrimonios') {
                axios.get(url)
                    .then(response => {
                        this.patrimonios = response.data.data;
                    }).catch(error => {
                    this.toastErrorApi(error);
                });
            },
            setOpenList(lista) {
                setTimeout(() => {
                    if (lista == "tipo" && this.$refs.selectTipo.isMenuActive == true) {
                        setTimeout(() => {
                            this.selTipoAberto = true;
                        }, 30);
                    }
                    if (lista == "visibilidade" && this.$refs.selectVisibilidade.isMenuActive == true) {
                        setTimeout(() => {
                            this.selVisibilidadeAberto = true;
                        }, 30);
                    }
                    if (lista == "alunos" && this.$refs.selectAlunos.isMenuActive == true) {
                        setTimeout(() => {
                            this.selAlunosAberto = true;
                        }, 30);
                    }
                    if (lista == "patrimonios" && this.$refs.selectPatrimonios.isMenuActive == true) {
                        setTimeout(() => {
                            this.selPatrimoniosAberto = true;
                        }, 30);
                    }
                }, 10)
            },
            closeLists() {
                if (this.selTipoAberto == true) {
                    this.selTipoAberto = false;
                    this.$refs.selectTipo.isMenuActive = false;
                }
                if (this.selVisibilidadeAberto == true) {
                    this.selVisibilidadeAberto = false;
                    this.$refs.selectVisibilidade.isMenuActive = false;
                }
                if (this.selAlunosAberto == true) {
                    this.selAlunosAberto = false;
                    this.$refs.selectAlunos.isMenuActive = false;
                }
                if (this.selPatrimoniosAberto == true) {
                    this.selPatrimoniosAberto = false;
                    this.$refs.selectPatrimonios.isMenuActive = false;
                }
            }
        },
        computed: {
            hasErrors: function () {
                if (!this.atividade.titulo || this.atividade.titulo.length > 255 || (!this.atividade.descricao ||
                    this.atividade.descricao.length < 25) || !this.atividade.visibilidade || !this.visibilidades.includes(this.atividade.visibilidade) ||
                    !this.tipoSelected || this.tipoSelected == 'outro' && (!this.outroTipo || this.outroTipo.length > 255)) {
                    return true;
                }
                this.atividade.patrimonios;
                this.atividade.participantes;
                if (!this.atividade.patrimonios || this.atividade.patrimonios.length == 0 || !this.atividade.participantes || this.atividade.participantes.length == 0 ||
                    this.chatExist && this.chatAssunto.length > 100) {
                    return true;
                }

                if (this.temGrupo && (!Number.isInteger(Number(this.numeroElementos)) || this.numeroElementos < 2
                    || this.numeroElementos > 6)) {
                    return true;
                }

                if (!this.dataInicio || (this.duracao == 2 && !this.dataFinal)) {
                    return true;
                }
                return false;

            },
            getTitle: function () {
                return this.isCreated() ? "Criar Atividade" : "Editar Atividade";
            },
            minDataFinal: function () {
                let data = new Date(this.dataInicio);
                data.setDate(data.getDate() + 1);
                return data.toISOString().substr(0, 10);
            }

        },
        watch: {
            turmasSelecionadas(novo, anterior){
                if (novo.length > anterior.length && novo.length - anterior.length == 1){
                    let inserido = novo.filter(x => !anterior.includes(x))[0];
                    inserido.alunos.forEach((aluno) => {
                        let index = this.atividade.participantes.findIndex(x => x.id === aluno.id);
                        if (index == -1){
                            this.atividade.participantes.push(aluno);
                        }
                    });
                } else{
                    let removido = anterior.filter(x => !novo.includes(x))[0];
                    if (removido == undefined){
                        return;
                    }
                    removido.alunos.forEach((aluno) => {
                        let index = this.atividade.participantes.findIndex(x => x.id === aluno.id);
                        this.atividade.participantes.splice(index, 1);
                    });
                }
            },
            atividade: function (atividade, oldAtividade) {
                if (atividade.id) {
                    if (this.atividade.chat && this.atividade.chat.id) {
                        this.chatAssunto = this.atividade.chat.assunto;
                        this.chatExist = true;
                    } else {
                        if (!this.atividade.chat || this.atividade.chat && !this.atividade.chat.assunto) {
                            this.chatAssunto = "";
                        }
                        this.chatExist = false;
                    }
                    if (this.atividade.numeroElementos == 1) {
                        this.temGrupo = false;
                    } else {
                        this.temGrupo = true;
                        this.numeroElementos = this.atividade.numeroElementos;
                    }

                    this.dataInicio = this.atividade.dataInicio;

                    if (this.atividade.dataFinal) {
                        this.dataFinal = this.atividade.dataFinal;
                        this.duracao = 2;
                    }

                    let myTipo = this.atividade.tipo;
                    let aux = 0;
                    this.tipos.forEach(function (tipo) {
                        if (tipo == myTipo) {
                            aux = 1;
                            return;
                        }
                    });

                    if (aux == 1) {
                        this.tipoSelected = this.atividade.tipo;
                    } else {
                        this.tipoSelected = "outro";
                        this.outroTipo = this.atividade.tipo;
                    }
                    this.turmasSelecionadas = this.atividade.turmas_participantes;
                } else {
                    this.cleanForm();
                }
            },
            dataInicio: function (newVal, oldVal) {
                if (this.duracao == 2 && this.dataFinal) {
                    let data1 = new Date(this.dataInicio);
                    let data2 = new Date(this.dataFinal);
                    if (data1.getTime() >= data2.getTime()) {
                        this.dataFinal = null;
                    }
                }
            }
        }
    }
</script>
