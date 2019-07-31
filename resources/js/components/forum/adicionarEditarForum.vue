<template>
    <div>
        <div class="modal fade" id="addForumModal" tabindex="-1" role="dialog" aria-labelledby="addForumModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addForumModal">{{getTitle()}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoading">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-container>
                            <v-layout class="form-group" row align-center>
                                <v-flex xs12 sm4>
                                    <v-text-field id="inputTitulo"
                                                v-model="forum.titulo"
                                                label="Titulo"
                                                :rules="[v => !!v || 'Titulo é obrigatório']"
                                                required
                                    ></v-text-field>
                                </v-flex>
                                <v-flex xs12 sm2></v-flex>
                                <v-flex xs12 sm4>
                                    <v-combobox
                                        v-model="forum.patrimonios"
                                        :items="patrimonios"
                                        item-text="nome"
                                        label="Patrimonios associados"
                                        multiple
                                        chips
                                        class="input-group--focused custom"
                                        deletable-chips
                                        :rules="[v => v.length != 0 || 'Patrimónios são obrigatórios']"
                                        hide-no-data
                                        required
                                        pa-0="" ma-0=""
                                        @input="forum.patrimonios = validarInputComboBox(forum.patrimonios)"
                                    >
                                        <template v-slot:append v-if="forum.patrimonios && forum.patrimonios.length == 0">
                                            <v-tooltip left>
                                                <template v-slot:activator="{ on }">
                                                    <v-icon v-on="on">help</v-icon>
                                                </template>
                                                Escolher patrimónios relacionados com o fórum
                                            </v-tooltip>
                                        </template>
                                        
                                        <template v-slot:selection="{ item, index }">
                                            <v-chip v-if="index === 0">
                                                <span>{{ item.nome }}</span>
                                            </v-chip>
                                            <span
                                                v-if="index === 1"
                                                class="grey--text caption"
                                            >(+{{ forum.patrimonios.length - 1 }} outros)</span>
                                        </template>
                                    </v-combobox>
                                </v-flex>
                            </v-layout>
                            <v-layout class="form-group" row align-center>
                                <v-text-field id="inputEmail"
                                            v-model="forum.userEmail"
                                            label="Email"
                                            :rules="emailRules"
                                            clearable
                                            required
                                ></v-text-field>
                                <v-switch
                                    v-model="forum.showEmail" :false-value="false" :true-value="true"
                                    :label="forum.showEmail ? 'Email visivel': 'Email não visivel'"
                                    color="primary"
                                >
                                    <template v-slot:append>
                                        <v-tooltip right v-if="!forum.showEmail">
                                            <template v-slot:activator="{ on }">
                                                <v-icon v-on="on">help</v-icon>
                                            </template>
                                            Os visitantes não conseguem ver o seu email
                                        </v-tooltip>
                                    </template>
                                </v-switch>
                                <v-flex xs12 sm2></v-flex>
                            </v-layout>
                            
                        </v-container>
                        <div class="grey--text">
                            Descrição
                        </div>
                        <div id="app">
                            <ckeditor :editor="editor" :config="editorConfig" :value="forum.descricao"
                                    v-model="forum.descricao" required></ckeditor>
                        </div>
                    </div>

                    <div v-if="!isLoading" class="modal-footer">
                        <div class="grey--text" v-if="!this.$store.state.user">
                            <v-btn v-if="!this.$store.state.user" class="btn btn-success" @click="gerarCodigo">Enviar código por email</v-btn>
                            Se ainda não tem o código de acesso, por favor confirme o seu email
                            <v-text-field class="text-sm-left"
                                v-model="forum.codigo"
                                label="Código de acesso"
                                :rules="[v => !!v || 'Código de acesso']"
                                clearable
                            >
                                <template v-slot:append v-if="!forum.codigo">
                                    <v-tooltip left>
                                        <template v-slot:activator="{ on }">
                                            <v-icon v-on="on">help</v-icon>
                                        </template>
                                        Insira aqui o seu código de acesso, tem de inserir o seu email e carregar em "Enviar código por email"
                                    </v-tooltip>
                                </template>
                            </v-text-field>
                        </div>
                        <div>
                            <button v-if="isCreated()" class="btn btn-info" v-on:click.prevent="save" 
                                :disabled="hasErrors || !this.$store.state.user && !forum.codigo">Registar</button>
                            <button v-else class="btn btn-info" v-on:click.prevent="update" :disabled="hasErrors || forum.codigo">Guardar</button>
                        </div>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                    <div v-else class="modal-footer">
                        <loader color="green" size="32px"></loader>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {

        props: ['forum', 'patrimonios'],

        data: function () {
            return {
                editor: ClassicEditor,
                editorConfig: {
                    toolbar: ['heading', '|', 'Bold', 'Italic', 'bulletedList', 'numberedList', 'blockQuote', 'Undo', 'Redo'],
                    heading: {
                        options: [
                            {model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph'},
                            {model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1'},
                            {model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2'},
                            {model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3'}
                        ]
                    },
                },
                emailRules: [
                    (v) => !!v || 'email é obrigatório',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                ],
                rules: {
                    length: len => v => (v || '').length <= len || `Numero de caracteres invalido, Maximo ${len}`,
                    required: v => !!v || 'Este campo é necessário'
                },
                isLoading: false
            };
        },
        methods: {
            isCreated() {
                return this.forum.id > 0 ? false : true;
            },
            getTitle() {
                return this.isCreated() ? "Adicionar Fórum" : "Editar Fórum";
            },
            getPatrimonios(){
                this.isLoading= true;
                axios.get('/api/patrimoniosShort')
                    .then(response => {
                        this.patrimonios = response.data.data;
                        this.isLoading= false;
                    })
                    .catch(error => {
                        this.toastErrorApi(error);
                        this.isLoading= false;
                    });
            },
            gerarCodigo(){
                this.isLoading= true;
                axios.post('/api/forums/generateAccessCode', this.forum).then(response => {
                    this.toastPopUp("success", "Enviámos-lhe um email com o código de acesso");
                    this.isLoading= false;
                }).catch(error => {
                    this.isLoading= false;
                    this.toastErrorApi(error);
                })
            },
            save: function () {
                this.isLoading= true;
                axios.post('/api/forums', this.forum).then(response => {
                    this.toastPopUp("success", "Fórum Criado!");
                    this.cleanForm();
                    this.$emit('getFor');
                    $('#addForumModal').modal('hide');
                    this.isLoading= false;
                }).catch(error => {
                    this.isLoading= false;
                    this.toastErrorApi(error);
                })
            },
            update() {
                this.isLoading= true;
                axios.post('/api/forums/' + this.forum.id, this.forum).then(response => {
                    this.toastPopUp("success", "Fórum Editado!");
                    this.cleanForm();
                    this.$emit('getFor');
                    $('#addForumModal').modal('hide');
                    this.isLoading= false;
                }).catch(error => {
                    this.isLoading= false;
                    this.toastErrorApi(error);
                });
            },
            cancel: function () {
                this.cleanForm();
                $('#addForumModal').modal('hide');
            },
            cleanForm: function () {
                this.forum.id = undefined;
                this.forum.titulo = "";
                this.forum.descricao = "";
                this.forum.userEmail = "";
                this.forum.showEmail = false;
                this.forum.patrimonios = [];
            },
        },
        computed: {
            hasErrors: function () {
                let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return !this.forum.titulo || !this.forum.descricao || !re.test(String(this.forum.userEmail).toLowerCase()) || !this.forum.patrimonios;
            }
        }
    };

</script>