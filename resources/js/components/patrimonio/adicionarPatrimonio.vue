<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="addPatrimonioModal" tabindex="-1" role="dialog" aria-labelledby="addPatrimonioModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPatrimonioModal">Criar Patrimonio</h5>
                        <button type="button" @click="item=null" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="form-group modal-footer">
                        <v-text-field id="inputNome"
                                      v-model="patrimonio.nome"
                                      label="Nome"
                                      :rules="[v => !!v || 'Nome é obrigatório']"
                                      required
                        ></v-text-field>
                    </div>
                    <!--<div id="app">
                        <ckeditor :editor="editor" :config="editorConfig" :value="editorData" v-model="editorData"></ckeditor>
                    </div>-->

                    <div class="form-group modal-footer">
                        <v-textarea
                            name="descricao"
                            v-model="patrimonio.descricao"
                            box
                            label="Descrição"
                            auto-grow
                            counter="3000"
                            :rules="[rules.length(3000)]"
                        ></v-textarea>
                    </div>

                    <div class="form-group modal-footer" >
                        <v-select
                            label="Distrito"
                            v-model="patrimonio.distrito"
                            :items="distritos"
                            :rules="[v => !!v || 'Distrito é obrigatório']"
                            class="input-group--focused"
                            required
                        ></v-select>
                    </div>
                    <div class="form-group modal-footer">
                        <v-select
                            label="Época"
                            v-model="patrimonio.epoca"
                            :items="epocas"
                            :rules="[v => !!v || 'Época histórica é obrigatória']"
                            class="input-group--focused"
                            required
                        ></v-select>
                    </div>
                    <div class="form-group modal-footer">
                        <v-select
                            label="Ciclo"
                            v-model="patrimonio.ciclo"
                            :items="ciclos"
                            :rules="[v => !!v || 'Ciclo é obrigatória']"
                            class="input-group--focused"
                            required
                        ></v-select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="save">Registar</button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    //import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    module.exports = {

        data: function () {
            return {
               /* valid: false,
                editor: ClassicEditor,
                editorData: '',
                editorConfig: {
                    toolbar: ['heading', '|', 'Bold', 'Italic', 'bulletedList', 'numberedList', 'blockQuote', 'Link', 'Undo', 'Redo'],

                    heading: {
                        options: [
                            {model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph'},
                            {model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1'},
                            {model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2'},
                            {model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3'}
                        ]
                    },
                },**/
                distritos: ['Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                    'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                    'Vila Real', 'Viseu', 'Açores', 'Madeira'],
                epocas: ['pré-história', 'idade antiga', 'idade média', 'idade contemporânea'],
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                patrimonio: {
                    nome: "",
                    descricao: "",
                    distrito: "",
                    epoca: "",
                    ciclo: ""
                },
                rules: {
                    length: len => v => (v || '').length <= len || `Numero de caracteres invalido, Maximo ${len}`,
                    required: v => !!v || 'Este campo é necessário'
                }
            };
        },
        methods: {
            handleFile: function (e) {
                var files = e.target.files || e.dataTransfer.files;
                if (files.length != 1) {
                    this.$toasted.error('Only one file allowed', {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
                if (!files[0].type.includes("image/")) {
                    this.$toasted.error('File must be an image', {
                        position: "bottom-center",
                        duration: 3000,
                    });
                    return;
                }
                this.photo_url = files[0];
            },
            formCreate: function () {
                let formData = new FormData();
                formData.append('name', this.name);
                formData.append('type', this.type);
                formData.append('description', this.description);
                formData.append('price', this.price);
                formData.append('photo_url', this.photo_url);
                return formData;
            },
            hasErrors: function () {
                if (!this.patrimonio.nome || !this.patrimonio.ciclo || !this.patrimonio.epoca || !this.patrimonio.distrito || !this.patrimonio.descricao) {
                    return true;
                }
                return false;
            },
            save: function () {
                if (!this.hasErrors()) {
                    axios.post('/api/patrimonios', this.patrimonio).then(response => {
                        this.toastPopUp("success", "Património Criado!");
                        this.$router.push('/admin/patrimonios');
                        $('#addPatrimonioModal').modal('hide');
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                    })
                }
            },
            cancel: function () {
                this.cleanForm();
                $('#addPatrimonioModal').modal('hide');
            },
            cleanForm: function () {
                this.patrimonio.nome = "";
                this.patrimonio.descricao = "";
                this.patrimonio.distrito = "";
                this.patrimonio.epoca = "";
                this.patrimonio.ciclo = "";
            }
        },
    };
</script>
