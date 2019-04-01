<template>
    <v-form ref="form">
        <br><br><br><br><br>

        <p>Nome do património: </p>
        <v-text-field
                v-model="patrimonio.nome"
                label="Nome"
                required
        ></v-text-field>

        <div id="app">
            <ckeditor :editor="editor" :config="editorConfig" :value="$parent.patrimonio.descricao"
                      v-model="editorData"  @ready="setData()"></ckeditor>
        </div>

        <v-container fluid grid-list-xl>
            <v-layout row wrap align-center>
                <v-flex xs12 sm3 d-flex>
                    <p>Distrito:</p>
                    <v-select
                            v-model="patrimonio.distrito"
                            :items="distritos"
                            :rules="[v => !!v || 'Item is required']"
                            class="input-group--focused"
                            required
                    ></v-select>
                </v-flex>
                <v-spacer></v-spacer>
                <v-flex xs12 sm3 d-flex>
                    <p>Época histórica:</p>
                    <v-select
                            v-model="patrimonio.epoca"
                            :items="epocas"
                            :rules="[v => !!v || 'Item is required']"
                            required
                    ></v-select>
                </v-flex>
                <v-spacer></v-spacer>
                <v-flex xs12 sm3 d-flex>
                    <p>Ciclo:</p>
                    <v-select
                            v-model="patrimonio.ciclo"
                            :items="ciclos"
                            :rules="[v => !!v || 'Item is required']"
                            required
                    ></v-select>
                </v-flex>
            </v-layout>
        </v-container>
        <div>
            <v-btn small v-on:click.prevent="update">Guardar</v-btn>
        </div>
    </v-form>
</template>
<script type="text/javascript">
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

    export default {
        data: function(){
            return {
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
                patrimonio: {
                    id: '',
                    nome: '',
                    descricao: '',
                    distrito: '',
                    epoca: '',
                    ciclo: ''
                },
            }
        },
        methods: {
            setData(){
                this.editorData = this.$parent.patrimonio.descricao;
            },
            update() {
                this.patrimonio.descricao = this.editorData;
                axios.put('/api/patrimonios/' + this.patrimonio.id, this.patrimonio).then(response=>{
                }).catch(function (error) {
                    console.log(error);
                });
            },
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