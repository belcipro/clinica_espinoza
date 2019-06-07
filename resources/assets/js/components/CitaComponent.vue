    
<template>
    <main class="main">
        <!-- Breadcrumb -->
        <div class="container-fluid">
            <!-- Ejemplo de tabla Listado -->
            <div class="card">
                <div class='demo-app'>
                    <div class='demo-app-top'>
                        <button @click="toggleWeekends"></button>
                        <button @click="gotoPast"></button>
                    </div>
                    <FullCalendar
                    :selectable="true"
                    :locale="'es'"
                    class='demo-app-calendar'
                    ref="fullCalendar"
                    defaultView="dayGridMonth"
                    :header="{
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    }"
                    :plugins="calendarPlugins"
                    :weekends="calendarWeekends"
                    :events="calendarEvents"
                    @dateClick="handleDateClick"
                    @select="handleSelect"
                    />
                </div>
            </div>
        </div>
        <div class="modal fade" tabindex="-1" :class="{'mostrar' : modal}" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-primary modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" v-text="tituloModal"></h4>
                            <button type="button" class="close" @click="cerrarModal()" aria-label="Close">
                              <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="form-horizontal">
                                <div class="form-group row">
                                    <label class="col-md-3 form-control-label" for="text-input">Titulo (*)</label>
                                    <div class="col-md-9">
                                       <input class="form-control" v-model="calendar.titulo"> 
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-md-3 form-control-label" for="text-input">Fecha de Inicio (*)</label>
                                    <div class="col-md-9">
                                        <div class="input-group mb-3">
                                            <date-picker v-model="calendar.dateStart" :config="optionsDate"></date-picker>
                                            
                                                <span class="spnaDate" id="basic-addon1">Hora</span>
                                            <date-picker v-model="calendar.timeStart" :config="optionsTime"></date-picker>
                                        </div>
                                    </div>
                                </div>
                                 
                                <div class="form-group row">
                                    <label class="col-md-3 form-control-label" for="text-input">Fecha fin (*)</label>
                                    <div class="col-md-9">
                                        <div class="input-group mb-3">
                                            <date-picker v-model="calendar.dateEnd" :config="optionsDate"></date-picker>
                                            
                                                <span class="spnaDate" id="basic-addon1">Hora</span>
                                            <date-picker v-model="calendar.timeEnd" :config="optionsTime"></date-picker>
                                        </div>
                                    </div>
                                </div>
                                <div  v-if="cita.create">
                                    <div class="form-group row">
                                        <label class="col-md-3 form-control-label" for="text-input">Buscar paciente</label>
                                        <div class="col-md-9">
                                            <search-autocomplete-component @selected="selectedUsuario"/>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12 text-center">
                                            <label class="radio-inline"><input v-model="tipoReserva" type="radio" name="tipo" value="cita">Cita</label>
                                            <label class="radio-inline"><input v-model="tipoReserva" type="radio" name="tipo" value="tratamiento">Tratamiento</label>
                                        </div>
                                    </div>
                                    <a href="#" class="btn btn-link" @click="createUsersTratamiento()" v-if="tipoReserva=='tratamiento'&&dataTratamientosUser.length==0">Crear tratamiento</a>
                                    <div class="form-group row" v-if="tipoReserva=='tratamiento'">
                                        <label class="col-md-3 form-control-label" for="text-input" >Seleccionar tratamiento</label>
                                        <div class="col-md-9">
                                            <div class="input-group mb-3">
                                            <select class="form-control" @change="listPago()" v-model="dataTratamientoUser">    
                                                <option value="">--Seleccione--</option>
                                                <option v-for="(item) in dataTratamientosUser" :key="item.id" :value="item">Inicio en {{item.fechaInicio}} - termina el {{item.fechaFin}}</option>
                                                
                                            </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 form-control-label" for="text-input">Pago (S/.)</label>
                                        <div class="col-md-9">
                                            <div class="input-group mb-3">
                                            <input class="form-control" v-model="tratamientoDetalle.pago">    
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                <hr>
                                <a class="btn btn-link" href="#" @click="AddCita()">{{txtCita}}</a>
                                <div v-show="errorCalendar" class="form-group row div-error">
                                    <div class="text-center text-error">
                                        <div v-for="error in errorMostrarMsjCalendar" :key="error" v-text="error">

                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="cerrarModal()">Cerrar</button>
                            <button type="button" v-if="tipoAccion==1" class="btn btn-primary" @click="registrarCalendario()">Guardar</button>
                            <button type="button" v-if="tipoAccion==2" class="btn btn-primary" @click="actualizarCalendario()">Actualizar</button>
                            <button type="button" v-if="tipoAccion==2" class="btn btn-danger" @click="eliminarCalendario()">Eliminar</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <form-tratamiento/>
    </main>
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

import {EventBus} from '../helpers/event-bus.js'
import SearchAutoCompleteComponent from "./inc/Search.vue"

import Cita from '../models/cita.js'
import TratamientoUsers from '../models/tratamiento_user.js'
import DetalleTratamiento from '../models/detalle_tratamiento.js'
import moment from "moment";
import datePicker from 'vue-bootstrap-datetimepicker';
import TratamientoUserForm from './inc/TratamientoUserForm.vue'
import toast from 'toastr';
import esLocale from '@fullcalendar/core/locales/es';

moment.locale('es');
export default {
  components: {
    FullCalendar,
    datePicker,
    'form-tratamiento':TratamientoUserForm,
    'search-autocomplete-component':SearchAutoCompleteComponent,
  },
  data: function() {
    return {
        tipoReserva:'cita',
        calendarPlugins: [ 
            dayGridPlugin,
            timeGridPlugin,
            interactionPlugin,
            
        ],
        selectedDate: {
            start: new Date(2018, 9, 16),
            end: new Date(2018, 9, 18)
        },
        date: moment(),
        calendar_id: 0,
        txtCita:'Crear cita',
        nombre : '',
        calendar:{
            titulo:'',
            dateStart:'',
            timeStart:'09:00:00',
            dateEnd: '',
            timeEnd:'22:00:00',
        },
        arrayCalendar : [],
        errorCalendar:null,
        modal : 0,
        tituloModal : '',
        tipoAccion : 0,
        dataTratamientoUser:null,
        cita:new Cita(),
        tratamientoUsers:new TratamientoUsers(),
        tratamientoDetalle:new DetalleTratamiento(),
        modalTratamiento:false,
        dataTratamientosUser:[],
        dataCita:[],
        calendarWeekends: true,
        errorMostrarMsjCalendar:[],
        calendarEvents: [ ],
        countCreate:true,
        optionsDate: {
            format: 'YYYY-MM-DD',
            collapse: true,
            useStrict:true,
        },
        optionsTime: {
            format: 'HH:mm:ss',
            collapse: true,
            useStrict:true,
        },
    }
  },
  created(){
      this.listCitas();
      this.listAgendado();
  },
  mounted(){
      
      EventBus.$on('tratamiento-created',data=>{
            TratamientoUsers.id_usuario= data.id_usuario;
        })
  },
  methods: {
    listPago:function(){
        this.tratamientoDetalle.id_tratamiento_usuario=this.dataTratamientoUser.id;
        this.tratamientoDetalle.pago=this.dataTratamientoUser.restante; 
    },
    listTratamientoUsers:function(){
        axios.get('/tratamiento-users?id_usuario='+this.tratamientoUsers.id_usuario)
        .then(res=>{
            const {data}=res;
            this.dataTratamientosUser=data;
        })
        .catch(err=>{

        })

    },
    selectedUsuario:function(item){
        this.tratamientoUsers.id_usuario=item.id;
        this.listTratamientoUsers();
    },
    AddCita(){
        if(this.cita.create==true){
            
            this.cita.create=false;
            this.txtCita='Crear cita';
            this.tituloModal='Registrar horario no disponible';
            return;
        }
        this.cita.create=true;
        this.txtCita='Crear horario no disponible';
        this.tituloModal='Registrar cita';
    },
    createUsersTratamiento(){
        const modal=true;
        EventBus.$emit('tratamiento-modal',modal);
    },
    createCita(){
        axios.post('cita-create-admin',{
            'cita':this.cita,
            'detalle':this.tratamientoDetalle,
            'tratamiento_users':this.tratamientoUsers,
        })
        .then(res=>{
            toast.success('Se guardo correctamente')
            this.modal=false;
            this.calendarEvents.push({start:this.cita.fechaInicio,end:this.cita.fechaFin,title:this.cita.titulo,backgroundColor:res.data.color,borderColor:res.data.color});
            this.countCreate=true;
        })
        .catch(err=>{
            toast.error('No se guardo correctamente')
            this.countCreate=true;
        })
    },
    createTratamiento(){
        if( this.tratamientoDetalle.pago>this.dataTratamientoUser.restante){
            return;
        }
        axios.post('tratamiento-detalle',{
            'cita':this.cita,
            'tratamiento_users':this.tratamientoUsers,
            'detalle':this.tratamientoDetalle,
        })
        .then(res=>{
            toast.success('Se guardo correctamente')
            this.modal=false;
            this.calendarEvents.push({start:this.cita.fechaInicio,end:this.cita.fechaFin,title:this.cita.titulo,backgroundColor:'#2EB598',borderColor:'#2EB598'});
            this.countCreate=true;
        })
        .catch(err=>{
            toast.error('No se guardo correctamente')
            this.countCreate=true;
        })
    },
    registrarCalendario(){
        console.log('click');
        if(this.countCreate==false){
            return;
        }
        this.countCreate=false;
        this.cita.titulo=this.calendar.titulo;
        this.cita.fechaInicio=this.calendar.dateStart+" "+this.calendar.timeStart;
        this.cita.fechaFin=this.calendar.dateEnd+" "+this.calendar.timeEnd;
        if (this.validarCalendar()){
            this.countCreate=true;
            return;
        }
        if(this.tipoReserva=='cita'&&this.cita.create==true){
            this.createCita();
            return;
        }
        if(this.tratamientoUsers!=0&&this.cita.create==true){
            this.createTratamiento();
            return;
        }
        
        
        let me = this;

        axios.post('/calendar/registrar',{
            'titulo': this.calendar.titulo,
            'fechaInicio': this.calendar.dateStart+' '+this.calendar.timeStart,
            'fechaFin': this.calendar.dateEnd+' '+ this.calendar.timeEnd,
            'color':'#757575',
        }).then(response=>{
            this.cerrarModal();
            const {data}=response;
            this.calendarEvents.push({start:data.fechaInicio,end:data.fechaFin,title:data.titulo,backgroundColor:data.color,borderColor:data.color});
            this.calendar.titulo='';
            toast.success('Se guardo correctamente')
            this.countCreate=true;
        }).catch(error=>{
            console.log(error);
            toast.error('No se guardo correctamente')
            this.countCreate=true;
        }); 
    },
    validarCalendar(){
        this.errorCalendar=0;
        this.errorMostrarMsjCalendar =[];
        if (!this.calendar.titulo) this.errorMostrarMsjCalendar.push("El Titulo no puede estar vacío.");
        if (!this.calendar.dateStart) this.errorMostrarMsjCalendar.push("La fecha de inicio no puede estar vacío.");
        if (!this.calendar.timeStart) this.errorMostrarMsjCalendar.push("La fecha final no puede estar vacío.");
        if (!this.calendar.dateEnd) this.errorMostrarMsjCalendar.push("La hora de inicio no puede estar vacío.");
        if (!this.calendar.timeEnd) this.errorMostrarMsjCalendar.push("La hora final no puede estar vacío.");
        if(this.cita.create==true){
            if (!this.tratamientoUsers.id_usuario) this.errorMostrarMsjCalendar.push("Busque un paciente.");
            if(this.tipoReserva=='tratamiento'){
                if (this.dataTratamientoUser==null) this.errorMostrarMsjCalendar.push("Seleccione o cree un tratamiento del paciente.");
            }
        }

        if (this.errorMostrarMsjCalendar.length) this.errorCalendar = 1;

        return this.errorCalendar;
    },

    actualizarCalendario(){
        if (this.validarCalendar()){
            return;
        }
        let me = this;
        axios.put('/calendar/actualizar',{
            'id': this.calendar_id,
            'titulo': this.calendar.titulo,
            'fechaInicio': this.calendar.dateStart+' '+this.calendar.timeStart,
            'fechaFin': this.calendar.dateEnd+' '+ this.calendar.timeEnd,
            'color':'#757575',
        }).then(function (response) {
            me.cerrarModal();
            me.calendar.titulo='';
            me.calendar_id=0;
            me.listarCalendar();
            toast.success('Exito al acctualizar');
        }).catch(function (error) {
            console.log(error);
            toast.error('Error al acctualizar');
        }); 
    },
    eliminarCalendario(){
        let me = this;
        console.log( this.calendar_id)
        axios.delete('/calendar/eliminar/'+this.calendar_id).then(function (response) {
            me.cerrarModal();
            me.calendar.titulo='';
            me.calendar_id=0;
            me.listarCalendar();id_usuario
            toast.success('Exito al eliminar');
        }).catch(function (error) {
            console.log(error);
            toast.error('Error al eliminar');
        }); 
    },
    cerrarModal(){
        this.modal=0;
        this.tituloModal='';
        this.errorCalendar=0;
    },
    abrirModal(modelo, accion, data = []){
        console.log(data)
        var newDate=new Date();
        var dateStart=new Date(data['dateStart']);
        console.log(dateStart,newDate);
        console.log(Date.parse(dateStart),Date.parse(newDate));
        if(Date.parse(dateStart)<Date.parse(newDate)){
            return false
        }
        switch(modelo){
            case "calendar":
            {
                switch(accion){
                    case 'registrar':
                    {
                        this.modal = 1;
                        this.tituloModal = 'Registrar horario no disponible';
                        this.tipoAccion = 1;
                        this.calendar.dateStart=data['dateStart'];
                        this.calendar.dateEnd=data['dateEnd'];
                        break;
                    }
                    case 'actualizar':
                    {
                        this.modal=1;
                        this.tituloModal = 'Actualizar';
                        this.tipoAccion = 2;
                        this.calendar.dateStart=moment(data['fechaInicio']).format('YYYY-MM-DD');
                        this.calendar.dateEnd=moment(data['fechaFin']).format('YYYY-MM-DD');
                        this.calendar.timeStart=moment(data['fechaInicio']).format('HH:mm:ss');
                        this.calendar.timeEnd=moment(data['fechaFin']).format('HH:mm:ss');
                        this.calendar.titulo=data['titulo'];
                        this.calendar_id=data['id'];
                        break;
                    }
                }
            }
        }
    },
    listCitas:function(){
        axios.get('/cita/citalist')
        .then(res=>{
            res.data.forEach(element=>{
                this.calendarEvents.push({start:element.fechaInicio,end:element.fechaFin,title:element.titulo,backgroundColor:element.color,borderColor:element.color});
            })
        })
        
    },
    listAgendado:function(){
        axios.get('/calendar/listall')
        .then(res=>{
            res.data.forEach(element=>{
                this.calendarEvents.push({start:element.fechaInicio,end:element.fechaFin,title:element.titulo,backgroundColor:element.color,borderColor:element.color});
            })
        })
    },
    toggleWeekends() {
      this.calendarWeekends = !this.calendarWeekends // update a property
    },
    gotoPast() {
      let calendarApi = this.$refs.fullCalendar.getApi() // from the ref="..."
      calendarApi.gotoDate('2000-01-01') // call a method on the Calendar object
    },
    handleDateClick(arg) {
      this.abrirModal('calendar', 'registrar', {'dateStart':moment(arg.date).format('YYYY-MM-DD'),'dateEnd':moment(arg.date).format('YYYY-MM-DD')} )
    },
    handleSelect(arg){
        this.calendar.timeStart=moment(arg.start).format('HH:mm:ss');
        this.calendar.timeEnd=moment(arg.end).format('HH:mm:ss');
        this.abrirModal('calendar', 'registrar', {'dateStart':moment(arg.start).format('YYYY-MM-DD'),'dateEnd':moment(arg.end).format('YYYY-MM-DD')} )
    }
  }
}
</script>

<style lang='scss'>
// you must include each plugins' css
// paths prefixed with ~ signify node_modules
@import '~@fullcalendar/core/main.css';
@import '~@fullcalendar/daygrid/main.css';
@import '~@fullcalendar/timegrid/main.css';
.demo-app {
  font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
  font-size: 14px;
}
.demo-app-top {
  margin: 0 0 3em;
}
.demo-app-calendar {
  margin: 0 auto;
  max-width: 900px;
}
</style>