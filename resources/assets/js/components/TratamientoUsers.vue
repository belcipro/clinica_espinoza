<template>
        <main class="main">
            <!-- Breadcrumb -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Escritorio</a></li>
            </ol>
            <div class="container-fluid">
                <!-- Ejemplo de tabla Listado -->
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> Tratamientos
                        <button type="button" @click="abrirModal('tratamiento','registrar')" class="btn btn-secondary">
                            <i class="icon-plus"></i>&nbsp;Nuevo
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select class="form-control col-md-3" v-model="criterio">
                                      <option value="nombre">Nombre</option>
                                    </select>
                                    <input type="text" v-model="buscar" @keyup.enter="listarTratamiento(1,buscar,criterio)" class="form-control" placeholder="Texto a buscar">
                                    <button type="submit" @click="listarTratamiento(1,buscar,criterio)" class="btn btn-primary"><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Precio</th>
                                    <th>Adelanto</th>
                                    <th>Restante</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tratamiento in arrayTratamiento" :key="tratamiento.id">
                                   
                                    <td>{{tratamiento.apellidosCli||""+" "+tratamiento.nombreCli}}</td>
                                    <td v-text="tratamiento.fechaInicio"></td>
                                    <td v-text="tratamiento.fechaFin"></td>
                                    <td v-text="'S/.'+tratamiento.precio"></td>
                                    <td v-text="'S/.'+tratamiento.adelanto"></td>
                                    <td v-text="'S/.'+tratamiento.restante"></td>
                                    <td>
                                        {{tratamiento.estado}}
                                        <button class="btn btn-warning" @click="updateStatus(tratamiento.id,'En proceso')" v-if="tratamiento.estado=='Finalizado'">
                                            En proceso
                                        </button>
                                        <button class="btn btn-success" @click="updateStatus(tratamiento.id,'Finalizado')" v-if="tratamiento.estado=='En proceso'">
                                            Finalizado
                                        </button>
                                        
                                            
                                    </td>

                                </tr>                                
                            </tbody>
                        </table>
                        <nav>
                            <ul class="pagination">
                                <li class="page-item" v-if="pagination.current_page > 1">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1,buscar,criterio)">Ant</a>
                                </li>
                                <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(page,buscar,criterio)" v-text="page"></a>
                                </li>
                                <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1,buscar,criterio)">Sig</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <!-- Fin ejemplo de tabla Listado -->
            </div>
            <!--Inicio del modal agregar/actualizar-->
            <form-tratamiento/>
            <!--Fin del modal-->
        </main>
</template>

<script>    
    import toastr from 'toastr'
    import TratamientoUserForm from './inc/TratamientoUserForm.vue'
    import {EventBus} from '../helpers/event-bus.js'

    export default {
        data (){
            return {
                tratamiento_id: 0,
                nombre : '',
                descripcion : '',
                arrayTratamiento : [],
                modal : 0,
                tituloModal : '',
                tipoAccion : 0,
                errorTratamiento : 0,
                errorMostrarMsjTratamiento : [],
                pagination : {
                    'total' : 0,
                    'current_page' : 0,
                    'per_page' : 0,
                    'last_page' : 0,
                    'from' : 0,
                    'to' : 0,
                },
                offset : 3,
                criterio : 'nombre',
                buscar : ''
            }
        },
        computed:{
            isActived: function(){
                return this.pagination.current_page;
            },
            //Calcula los elementos de la paginación
            pagesNumber: function() {
                if(!this.pagination.to) {
                    return [];
                }
                
                var from = this.pagination.current_page - this.offset; 
                if(from < 1) {
                    from = 1;
                }

                var to = from + (this.offset * 2); 
                if(to >= this.pagination.last_page){
                    to = this.pagination.last_page;
                }  

                var pagesArray = [];
                while(from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;             

            }
        },
        methods : {
            updateStatus:function(id,data){
                axios.post('/tratamiento-users-status',{
                    'id':id,
                    'estado':data
                })
                .then(res=>{
                    toastr.success('Se guardo correctamente');
                    this.listarTratamiento(1,this.buscar,this.criterio);
                })
                .catch(err=>{
                    toastr.error('No se guardo correctamente');
                })
            },
            listarTratamiento (page,buscar,criterio){
                let me=this;
                var url= '/tratamiento-users?page=' + page + '&buscar='+ buscar + '&criterio='+ criterio;
                axios.get(url)
                .then(function (response) {
                    var respuesta = response.data;
                    me.arrayTratamiento = respuesta.tratamientos.data;
                    me.pagination = respuesta.pagination;
                })
                .catch(function (error) {
                    console.log(error);
                });
            },
            cambiarPagina(page,buscar,criterio){
                let me = this;
                //Actualiza la página actual
                me.pagination.current_page = page;
                //Envia la petición para visualizar la data de esa página
                me.listarTratamiento(page,buscar,criterio);
            },
            registrarTratamiento(){
                if (this.validarTratamiento()){
                    return;
                }
                
                let me = this;

                axios.post('/tratamiento/registrar',{
                    'nombre': this.nombre,
                    'descripcion' : this.descripcion
                }).then(function (response) {
                    me.cerrarModal();
                    me.listarTratamiento(1,'','nombre');
                }).catch(function (error) {
                    console.log(error);
                });
            },
            actualizarTratamiento(){
               if (this.validarTratamiento()){
                    return;
                }
                
                let me = this;

                axios.put('/tratamiento/actualizar',{
                    'nombre': this.nombre,
                    'descripcion' : this.descripcion,
                    'id': this.tratamiento_id
                }).then(function (response) {
                    me.cerrarModal();
                    me.listarTratamiento(1,'','nombre');
                }).catch(function (error) {
                    console.log(error);
                }); 
            }, 
            validarTratamiento(){
                this.errorTratamiento=0;
                this.errorMostrarMsjTratamiento =[];

                if (!this.nombre) this.errorMostrarMsjTratamiento.push("El nombre del tratamiento no puede estar vacío.");

                if (this.errorMostrarMsjTratamiento.length) this.errorTratamiento = 1;

                return this.errorTratamiento;
            },
            cerrarModal(){
                this.modal=0;
                this.tituloModal='';
                this.nombre='';
                this.descripcion='';
                this.errorTratamiento=0;
            },
            abrirModal(modelo, accion, data = []){
                const modal=true;
                EventBus.$emit('tratamiento-modal',modal);
            }
        },
        components: {
            'form-tratamiento':TratamientoUserForm,
        },
        mounted() {
            this.listarTratamiento(1,this.buscar,this.criterio);
            EventBus.$on('tratamiento-created',data=>{
                this.listarTratamiento(1,this.buscar,this.criterio);
            });
        }
    }
</script>