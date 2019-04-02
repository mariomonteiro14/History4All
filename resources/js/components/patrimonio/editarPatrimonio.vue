<template>
    <v-form ref="form" v-model="valid" lazy-validation>
        <v-app id="inspire">
            <br><br><br><br><br>
            <h3>Patrimónios / Editar</h3>
            <br>
            <v-card append float>
                <v-card-title>
                    <v-container fluid grid-list-xl>
                        <v-layout row wrap align-center>
                            <v-text-field
                                    v-model="patrimonio.nome"
                                    label="Nome"
                                    :rules="[v => !!v || 'Nome é obrigatório']"
                                    required
                            ></v-text-field>
                        </v-layout>
                    </v-container>
                    <v-container fluid grid-list-xl>
                        <v-layout row wrap align-center>
                            <div class="form-group" >
                                <v-select
                                        label="Distrito"
                                        v-model="patrimonio.distrito"
                                        :items="distritos"
                                        :rules="[v => !!v || 'Distrito é obrigatório']"
                                        class="input-group--focused"
                                        required
                                ></v-select>
                            </div>
                            <v-spacer></v-spacer>
                            <div class="form-group">
                                <v-select
                                        label="Época"
                                        v-model="patrimonio.epoca"
                                        :items="epocas"
                                        :rules="[v => !!v || 'Época histórica é obrigatória']"
                                        class="input-group--focused"
                                        required
                                ></v-select>
                            </div>
                            <v-spacer></v-spacer>
                            <div class="form-group">
                                <v-select
                                        label="Ciclo"
                                        v-model="patrimonio.ciclo"
                                        :items="ciclos"
                                        :rules="[v => !!v || 'Ciclo é obrigatória']"
                                        class="input-group--focused"
                                        required
                                ></v-select>
                            </div>
                        </v-layout>
                    </v-container>
                </v-card-title>
                <div id="app">
                    <ckeditor :editor="editor" :config="editorConfig" :value="$parent.patrimonio.descricao"
                              v-model="editorData"  @ready="setData()"></ckeditor>
                </div>
                <div class="text-xs-center">
                    <v-btn color="success" @click="validate">Guardar</v-btn>
                    <v-btn color="error" @click="cancelar">Cancelar</v-btn>
                </div>
            </v-card>
        </v-app>
    </v-form>
</template>
<script type="text/javascript">
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        data: function(){
            return {
                valid: false,
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
                },
                distritos: ['Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra', 'Évora', 'Faro',
                    'Guarda', 'Leiria', 'Lisboa', 'Portalegre', 'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo',
                    'Vila Real', 'Viseu', 'Açores', 'Madeira'],
                epocas: ['pré-história', 'idade antiga', 'idade média', 'idade contemporânea'],
                ciclos: ['1º ciclo', '2º ciclo', '3º ciclo', 'secundário'],
                patrimonio: {},
            }
        },
        methods: {
            setData(){
                this.editorData = this.$parent.patrimonio.descricao;
            },
            update() {
                this.patrimonio.descricao = this.editorData;
                axios.put('/api/patrimonios/' + this.patrimonio.id, this.patrimonio).then(response=>{
                    this.toastPopUp("success", "Património Editado!");
                    this.$router.push('/admin/patrimonios');
                }).catch(function (error) {
                    this.toastPopUp("error", "Erro");
                    console.log(error);
                });
            },
            validate () {
                if (this.$refs.form.validate()) {
                    if (this.editorData === ""){
                        this.toastPopUp("error", "Descrição é obrigatória");
                    }
                    this.valid = true;
                    this.update();
                }
            },
            cancelar(){
                this.$router.push('/admin/patrimonios');
            }
        },
        mounted() {
            this.patrimonio = this.$parent.patrimonio;
        },
        watch: {
            editorData: function( val ) {
                this.$emit('input', val);
            },
        }
    }
</script>
<style>
    .ck-editor__editable {
        min-height: 300px;
    }
</style>