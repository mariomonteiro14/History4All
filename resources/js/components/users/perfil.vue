<template>
    <div>
        <br><br><br><br><br>
        <link href="/css/profile.css" rel="stylesheet" type="text/css">
        <br>
        <div v-if="isLoading">
            <br><br><br>
            <v-progress-linear v-show="isLoading" v-slot:progress :color="colorDefault"
                               indeterminate></v-progress-linear>
            <br><br><br><br><br><br><br><br><br>
        </div>
        <div v-if="!isLoading && !profileUser.id">
            <br><br><br>
            <v-card>
                <v-container fluid grid-list-md>
                    <v-alert :value="true" color="red" icon="warning">
                        <div v-if="profileUser">
                            {{profileUser}}
                        </div>
                        <div v-else>
                            Utilizador nao encontrado! :(
                        </div>
                    </v-alert>
                </v-container>
            </v-card>
            <br><br><br><br><br><br><br><br><br>
        </div>
        <div class="container emp-profile" v-if="profileUser.id">
            <div class="row">
                <div class="col-md-4">
                    <div class="profile-img">
                        <v-avatar size="150px" :color="colorDefault">
                            <img v-if="profileUser.foto != null" v-bind:src="getUserPhoto(profileUser.foto)" alt=""/>
                            <v-icon v-else class="white--text" large>far fa-user</v-icon>
                        </v-avatar>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="profile-head">
                        <h5>
                            {{profileUser.nome}}
                        </h5>
                        <h6>
                            {{profileUser.tipo.charAt(0).toUpperCase() + profileUser.tipo.slice(1)}}
                        </h6>
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                   aria-controls="home" aria-selected="true">Sobre</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <button v-if="profileUser.id == $store.state.user.id"
                            class="profile-edit-btn" name="btnAddMore" href="#editProfileModal"
                            data-toggle="modal" data-target="#editProfileModal">Editar Perfil
                    </button>
                    <button v-else class="profile-edit-btn" name="btnAddMore" href="#contactModal"
                            data-toggle="modal" data-target="#contactModal">Contactar
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="profile-work">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="tab-content profile-tab" id="myTabContent">
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-md-2">
                                    <label>Nome:</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.nome}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label>Email:</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.email}}</p>
                                </div>
                            </div>
                            <div v-if="profileUser.tipo != 'admin'" class="row">
                                <div class="col-md-2">
                                    <label>Escola:</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.escola[0]}}</p>
                                </div>
                            </div>
                            <div v-if="profileUser.tipo == 'aluno'" class="row">
                                <div class="col-md-2">
                                    <label>Turma:</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{profileUser.turma[0]}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <v-layout align-content-start v-if="!isLoading && id">
            <v-flex xs1 offset-sm1>
                <v-btn class="primary--text subheading" round flat @click="$router.go(-1)">
                    <v-icon>fa fa-arrow-left</v-icon>
                    &nbsp Voltar
                </v-btn>
            </v-flex>
        </v-layout>
        <br><br><br>
        <edit-profile @reloadUser="profileUser = $store.state.user"></edit-profile>
        <contact-user v-if="profileUser.email" :email-to="profileUser.email"></contact-user>
    </div>
</template>
<script type="text/javascript">
    import editProfile from './editPerfil.vue';
    import contactUser from '../widgets/contactForm.vue';


    export default {
        props: ['id'],
        components: {
            'edit-profile': editProfile,
            'contact-user': contactUser,
        },
        mounted() {
            if (this.id) {
                this.isLoading = true;
                axios.get('/api/user/' + this.id)
                    .then(response => {
                        this.profileUser = response.data.data ? response.data.data : 'Não tem permissão para visualizar o utilizador';
                        this.isLoading = false;
                    }).catch(error => {
                    this.toastErrorApi(error);
                    this.isLoading = false;
                });
            } else {
                this.profileUser = this.$store.state.user;
            }
        },
        data: function () {
            return {
                profileUser: '',
                successMessage: "",
                showSuccess: false,
                isLoading: false,
            }
        },

        methods: {},
        watch: {
            id: function () {
                if (this.id) {
                    this.isLoading = true;
                    axios.get('/api/user/' + this.id)
                        .then(response => {
                            this.profileUser = response.data.data;
                            this.isLoading = false;
                        }).catch(error => {
                        this.toastErrorApi(error);
                        this.isLoading = false;
                    });
                } else {
                    this.profileUser = this.$store.state.user;
                }
            }
        }
    }
</script>
