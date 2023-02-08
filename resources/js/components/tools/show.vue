<template>
    <div>
        <v-dialog v-model="active">
            <v-card>
                <v-card-title>Esta usted seguro?</v-card-title>
                <v-card-actions>
                    <v-btn color="success" text @click.prevent="update">Guardar</v-btn>
                    <v-btn color="error" text @click="active = false">Cancelar/Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="show" v-if="tool !== null" scrollable>
            <v-card>
                <v-card-title>Herramienta {{ tool.item }}</v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form v-model="valid">
                        <v-row>
                            <v-col cols="4">
                                <v-combobox label="Equipo/Set" v-model="tool.set" item-text="name" :items="sets" clearable item-value="name"></v-combobox>
                            </v-col>
                            <v-col cols="4">
                                <v-combobox label="Descripcion/Description" v-model="tool.des" item-text="name" :items="dess" clearable item-value="name"></v-combobox>
                            </v-col>
                            <v-col cols="4">
                                <v-combobox label="Marca/Brand" v-model="tool.brand" item-text="name" :items="brands" :rules="[rules.required]" clearable item-value="name" disabled></v-combobox>
                            </v-col>
                            <v-col cols="4">
                                <v-combobox label="Calibracion/Calibration Due" v-model="tool.calibration" item-text="name" :items="calibrations" :rules="[rules.required]" clearable item-value="name" disabled></v-combobox>
                            </v-col>
                            <v-col cols="4">
                                <v-combobox label="Localizacion/Location" v-model="tool.location" item-text="name" :items="locations" :rules="[rules.required]" clearable item-value="name" disabled></v-combobox>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="4">
                                <v-text-field label="Cantidad" v-model.number="tool.quantity" :rules="[rules.required, v => v > 0 || 'Cantidad invalida']"></v-text-field>
                            </v-col>
                            <v-col cols="4">
                                <v-text-field label="Unidad de contaje" v-model="tool.measurement" :rules="[rules.required]" disabled></v-text-field>
                            </v-col>
                            <v-col cols="4">
                                <v-text-field label="N de Serie" v-model="tool.serial" disabled></v-text-field>
                            </v-col>
                            <v-col cols="4">
                                <v-text-field label="Caracteristicas" v-model="tool.spect" disabled></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="4">
                                <v-text-field label="Modelo" v-model="tool.model"></v-text-field>
                            </v-col>
                            <v-col cols="4">
                                <v-text-field label="Localizacion 2" v-model="tool.shelf_localization"></v-text-field>
                            </v-col>
                            <v-col cols="4">
                                <v-textarea label="Comentarios" v-model="tool.comments" :rows="1"></v-textarea>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn text color="success" @click="active = true">Guardar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import { getToken } from "../../lib/auth";
import { required } from "../../lib/rules";
import vueFilePond, { setOptions } from "vue-filepond";
import "filepond/dist/filepond.min.css"
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type"
const FilePond = vueFilePond(FilePondPluginFileValidateType);

export default {
    name: "show",
    data: () => ({
        active: false,
        loading: false,
        valid: false,
        show: false,
        tool: null,
        movingQuantity: 0,
        menu: false,
        rules : { required: required },
        groups: [],
        families: [],
        brands: [],
    }),
    methods: {
        async update() {
            this.active = false
            this.tool = { ...this.tool, movingQuantity: this.movingQuantity }
            const response = await axios.put(`/api/tools/${this.tool.id}`, this.tool, getToken())
            if (response.status === 200) {
                const newItem = {
                    id: this.tool.id,
                    item: this.tool.item,
                    set: this.tool.set,
                    des: this.tool.des,
                    brand: this.tool.brand,
                    calibration: this.tool.calibration,
                    location: this.tool.location,
                    quantity: this.tool.quantity
                }
                this.$emit('updated', newItem)
                this.show = false
                this.movingQuantity = 0
            }

        },
        async open(tool) {
            const response = await axios.get(`/api/tools/${tool.id}`, getToken())
            this.tool = response.data
            this.show = true
        }
    },
    computed: {
        disabled() {
            if (this.tool.hasValidation && this.tool.calibrationExpiration === null) {
                return true
            }
            return !this.valid
        }
    },
    async mounted() {
        setOptions({
            server: {
                url: '/api/uploads',
                withCredentials: true,
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`
                }
            }
        })
        await axios.get('/api/groups', getToken()).then(response => this.groups =  response.data )
        await axios.get('/api/families', getToken()).then(response => this.families = response.data)
        await axios.get('/api/brands', getToken()).then(response => this.brands = response.data)
        this.loading = false
    },
    components: {
        FilePond
    }
}
</script>
