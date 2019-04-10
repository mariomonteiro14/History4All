<template>
    <div id="app">
        <br><br><br><br>

        <v-app id="inspire">
            <v-layout align-content-center>
                <v-flex xs12 sm7 offset-sm3>
                    <v-card>
                        <v-img v-if="patrimonio.imagens && patrimonio.imagens.length >0"
                            :src="getPatrimonioPhoto(patrimonio.imagens[0].foto)"
                            aspect-ratio="2.5"
                            alt="patrimonio.nome"
                        ></v-img>

                        <v-card-title primary-title>
                            <div>
                                <h3 class="headline mb-0">{{patrimonio.nome}}</h3>
                                <span class="grey--text">{{patrimonio.distrito}}</span>
                                <br><br>
                                <div v-html="patrimonio.descricao"></div>
                            </div>
                        </v-card-title>

                        <v-card-actions>
                            <v-btn flat color="orange" v-if="patrimonio.imagens && patrimonio.imagens.length > 1">Galeria</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-app>
        <br><br>
    </div>

</template>
<script type="text/javascript">
    export default {
        props: ['id'],
        data: function(){
            return {
                patrimonio: [],
            };
        },
        methods: {
            getPatrimonio() {
                axios.get('/api/patrimonios/' + this.id)
                    .then(response => {
                        this.patrimonio = response.data;
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            }
        },
        mounted() {
            this.getPatrimonio();
        }
    }
</script>
