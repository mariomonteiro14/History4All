<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="addAtividadeModal" tabindex="-1" role="dialog" aria-labelledby="addAtividadeModal"
             aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box" @click="closeLists">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addAtividadeModal">{{getTitle}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <br>
                        <div class="form-group">
                            <v-text-field id="inputTitulo"
                                          v-model="atividade.titulo"
                                          label="Título"
                                          :rules="[v => !!v || 'Titulo é obrigatório']"
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group">
                            <v-textarea
                                    id="inputDescricao"
                                    v-model="atividade.descricao"
                                    label="Descrição"
                                    :rules="[v => !!v || 'Descrição é obrigatória']"
                                    required
                            ></v-textarea>
                        </div>
                        <div @click="setOpenList('tipo')">
                            <v-select
                                    label="Tipo"
                                    v-model="atividade.tipo"
                                    :items="tipos"
                                    :rules="[v => !!v || 'Tipo de atividade é obrigatório']"
                                    class="input-group--focused"
                                    clearable
                                    ref="selectTipo"
                                    required
                            ></v-select>
                        </div>
                        <div class="form-group">
                            <v-text-field
                                    type="number"
                                    min="1"
                                    step="1"
                                    default="1"
                                    @change="atividade.numeroElementos = Math.max(Math.min(Math.round(atividade.numeroElementos), 99), 1)"
                                    id="inputNumeroElementos"
                                    v-model="atividade.numeroElementos"
                                    label="Numero de elementos por grupo (1 caso seja individual)"
                                    :rules="[v => !!v && !isNaN(v) || 'Numero de elementos é obrigatório']"
                                    required
                            ></v-text-field>
                        </div>
                        <div class="form-group">
                            <v-checkbox
                                    v-model="chatExist"
                                    label="Atividade tem Chat?"
                            ></v-checkbox>
                        </div>

                        <div class="form-group" v-if="chatExist">
                            <v-textarea
                                    id="inputChatAssunto"
                                    v-model="atividade.chat.assunto"
                                    label="Chat Descrição"
                                    :rules="[v => !!v || 'Assunto do chat é obrigatória']"
                                    required
                            ></v-textarea>
                        </div>
                        <div @click="setOpenList('visibilidade')">
                            <v-select
                                    label="Visibilidade"
                                    v-model="atividade.visibilidade"
                                    :items="visibilidades"
                                    :rules="[v => !!v || 'Visibilidade é obrigatória']"
                                    class="input-group--focused"
                                    required
                                    clearable
                                    ref="selectVisibilidade"
                            ></v-select>
                        </div>
                        <div @click="setOpenList('alunos')" v-if="atividade.visibilidade == 'privado'">
                            <v-combobox
                                    v-model="atividade.participantes"
                                    :items="alunos"
                                    item-text="nome"
                                    label="Alunos"
                                    multiple
                                    chips
                                    class="input-group--focused"
                                    deletable-chips
                                    ref="selectAlunos"
                                    autofocus
                                    hide-no-data
                            ></v-combobox>
                        </div>
                        <div @click="setOpenList('patrimonios')">
                            <v-combobox
                                    v-model="patrimoniosSelecionados"
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
                            ></v-combobox>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button v-if="!atividade.id" class="btn btn-info" v-on:click.prevent="save" :disabled="hasErrors">
                            Registar
                        </button>
                        <button v-else class="btn btn-info" v-on:click.prevent="edit" :disabled="hasErrors">
                            Guardar
                        </button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        props: ['atividade'],
        created() {
            this.getAlunos();
            this.getPatrimonios();
        },
        data: function () {
            return {
                tipos: ['visita de estudo', 'trabalho em familia', 'trabalho de pesquisa', 'definir tipos de patrimonio'],
                visibilidades: ['privado', 'publico', 'visivel para a escola'],
                alunos: [],
                patrimonios: [],
                selTipoAberto: false,
                selVisibilidadeAberto: false,
                selAlunosAberto: false,
                selPatrimoniosAbertos: false,
                reseted: false,
                chatExist: false,
                patrimoniosSelecionados: []
            };
        },
        methods: {
            isCreated() {
                return this.atividade.id > 0 ? false : true;
            },
            save: function () {
                this.prepararAtividade();
                axios.post('/api/atividades', this.atividade).then(response => {
                    this.toastPopUp("success", "Atividade Criada!");
                    this.cleanForm();
                    this.$emit('getAtividades');
                    $('#addAtividadeModal').modal('hide');
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            edit: function () {
                this.prepararAtividade();
                axios.put('/api/atividades/' + this.atividade.id, this.atividade).then(response => {
                    this.toastPopUp("success", "Atividade Atualizada!");
                    this.cleanForm();
                    this.$emit('getAtividades');
                    $('#addAtividadeModal').modal('hide');
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            prepararAtividade(){
                this.atividade.chatExist = this.chatExist;
                this.atividade.patrimonios = this.patrimoniosSelecionados;
                if (this.atividade.visibilidade == 'privado'){
                    this.participantes = [];
                }
            },
            cancel: function () {
                this.cleanForm();
                $('#addAtividadeModal').modal('hide');
            },
            cleanForm: function () {
                this.atividade.id = null;
                this.atividade.titulo = "";
                this.atividade.descricao = "";
                this.atividade.tipo = "";
                this.atividade.numeroElementos = "";
                this.atividade.visibilidade = "";
                this.atividade.data = "";
                this.atividade.chat = {assunto: ""};
                this.atividade.participantes = [];
                this.atividade.patrimonios = [];
                this.patrimoniosSelecionados = [];
                this.closeLists();
                this.chatExist = false;
                this.reseted = true;
            },
            getAlunos(url = '/api/users/alunos') {
                if (this.$store.state.user.tipo == 'professor'){
                    url = "/api/me/escola/alunos";
                }
                axios.get(url)
                    .then(response => {
                        this.alunos = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            getPatrimonios(url = '/api/patrimonios') {
                axios.get(url)
                    .then(response => {
                        this.patrimonios = response.data.data;
                    })
                    .catch(errors => {
                        console.log(errors);
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
            },
            formatarAtividade(){
                if (!this.atividade.id && !this.reseted){
                    this.cleanForm();
                } else {
                    if (this.atividade.id){
                        this.patrimoniosSelecionados = this.atividade.patrimonios;
                    }
                    this.reseted = false;
                }
                if (this.atividade.chat && this.atividade.chat.id){
                    this.chatExist = true;
                } else {
                    if (!this.atividade.chat || this.atividade.chat && !this.atividade.chat.assunto){
                        this.atividade.chat = {assunto: ""};
                    }
                    this.chatExist = false;
                }
            }
        },
        computed: {
            hasErrors: function () {
                return (!this.atividade.titulo || !this.atividade.descricao ||
                    !this.atividade.tipo || !this.atividade.numeroElementos ||
                    !this.atividade.visibilidade || this.chatExist && !this.atividade.chat.assunto ||
                    this.patrimoniosSelecionados.length === 0);
            },
            getTitle: function() {
                this.formatarAtividade();
                return this.isCreated() ? "Criar Atividade" : "Editar Atividade";
            },
        },

    }
</script>
