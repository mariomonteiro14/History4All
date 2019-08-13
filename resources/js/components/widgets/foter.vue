<template>
    <div>
        <v-footer dark height="auto"
        >
            <v-card
                class="flex"
                flat
                tile
                absolute
            >
                <v-card-title class="light-green darken-3">
                    <strong class="subheading">Partilhe a nossa pagina nas redes sociais!</strong>

                    <v-spacer></v-spacer>

                    <social-sharing ref="socialSharing" :url="this.url"
                                    title="History4All - Site de Historia"
                                    description="Portal de historia dinamico desenhado para alunos e professores."
                                    quote="Portal de historia dinamico desenhado para alunos e professores."
                                    twitter-user="History4All"
                                    inline-template>
                        <div>
                            <network network="facebook">
                                <v-btn icon flat>
                                    <v-icon medium>fab fa-facebook</v-icon>
                                </v-btn>
                            </network>
                            <network network="twitter">
                                <v-btn icon flat>
                                    <v-icon medium>fab fa-twitter</v-icon>
                                </v-btn>
                            </network>
                            <network network="linkedin">
                                <v-btn icon flat>
                                    <v-icon medium>fab fa-linkedin</v-icon>
                                </v-btn>
                            </network>
                        </div>
                    </social-sharing>
                </v-card-title>

                <v-card-actions class="green darken-1 justify-center">
                    &copy;2019 - &nbsp <strong>History4All</strong>
                </v-card-actions>

            </v-card>
        </v-footer>
    </div>
</template>

<script>
    import '@fortawesome/fontawesome-free/css/all.css' // Ensure you are using css-loader

    export default {

        data: function () {
            return {
                links: [
                    'Home',
                    'About Us',
                    'Team',
                    'Services',
                    'Blog',
                    'Contact Us'
                ],
                icons: [
                    'fab fa-facebook',
                    'fab fa-twitter',
                    'fab fa-google-plus',
                    'fab fa-instagram'
                ],
            }
        },
        computed: {
            url(){
                //console.log(this.$route.name);
                if (this.$route.name == "patrimoniosShow" || this.$route.name == "patrimonios" ||
                    this.$route.name == "forums" || this.$route.name == "showForum" ) {
                    return  window.location.href;
                }else{
                    return 'http://142.93.219.146/';
                }
            }
        }



    }
</script>
