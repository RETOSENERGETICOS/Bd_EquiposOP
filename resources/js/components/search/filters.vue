<template>
    <v-expansion-panels v-model="panel">
        <v-expansion-panel>
            <v-expansion-panel-header>Filtros</v-expansion-panel-header>
            <v-expansion-panel-content>
                <v-row>
                    <v-col>
                        <active-filters />
                    </v-col>
                    <v-col>
                        <v-btn color="success" text @click.prevent="search">Aplicar filtros <v-icon>mdi-download</v-icon></v-btn>
                    </v-col>
                    <v-col>
                        <v-btn color="cyan" text @click.prevent="history">Consultar Historial <v-icon>mdi-history</v-icon></v-btn>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="4" v-if="filters.set.active"><v-select v-model="filter.set" label="Equipo" :items="sets" item-text="name" return-object clearable></v-select></v-col>
                    <v-col cols="4" v-if="filters.des.active"><v-select v-model="filter.des" label="Descripcion/Description" :items="dess" item-text="name" return-object clearable></v-select></v-col>
                    <v-col cols="4" v-if="filters.brand.active"><v-select v-model="filter.brand" label="Marca/Brand" :items="brands" item-text="name" return-object clearable></v-select></v-col>
                    <v-col cols="4" v-if="filters.calibration.active"><v-select v-model="filter.calibration" label="Calibracion" :items="calibrations" item-text="name" return-object clearable></v-select></v-col>
                    <v-col cols="4" v-if="filters.location.active"><v-select v-model="filter.location" label="Localizacion" :items="locations" item-text="name" return-object clearable></v-select></v-col>

                    <v-col cols="4" v-if="filters.quantity.active"><v-text-field v-model.number="filter.quantity" label="Cantidad" clearable></v-text-field></v-col>
                    <v-col cols="4" v-if="filters.measurement.active"><v-text-field v-model="filter.measurement" label="Medida" clearable></v-text-field></v-col>
                    <v-col cols="4" v-if="filters.serialNumber.active"><v-text-field v-model="filter.serialNumber" label="Serie" clearable></v-text-field></v-col>
                    <v-col cols="4" v-if="filters.spect.active"><v-text-field v-model="filter.spect" label="Caracteristicas" clearable></v-text-field></v-col>

                    <v-col cols="4" v-if="filters.model.active"><v-text-field v-model="filter.model" label="Modelo" clearable></v-text-field></v-col>
                    <v-col cols="4" v-if="filters.shelfLocalization.active"><v-text-field v-model="filter.shelfLocalization" label="Localizacion de estante" clearable></v-text-field></v-col>
                    <v-col cols="4" v-if="filters.item.active"><v-text-field v-model="filter.item" label="Item" clearable></v-text-field></v-col>
                    <v-col cols="4" v-if="filters.user.active"><v-select v-model="filter.user" label="Usuario" :items="users" item-text="email" return-object clearable></v-select></v-col>
                </v-row>
            </v-expansion-panel-content>
        </v-expansion-panel>
    </v-expansion-panels>
</template>

<script>
import { getToken } from "../../lib/auth";
import activeFilters from "./activeFilters";
import { mapGetters } from "vuex"

export default {
    name: "filters",
    data: () => ({
        panel: 0,
        sets: [{id: 0, name: 'TODOS'}],
        dess: [{id: 0, name: 'TODOS'}],
        brands: [{id: 0, name: 'TODOS'}],
        calibrations: [{id: 0, name: 'TODOS'}],
        locations: [{id: 0, name: 'TODOS'}],
        users: [{id: 0, email: 'TODOS'}],
        menu: false,
        filter: {
            set: null,
            des: null,
            brand: null,
            calibration: null,
            location: null,
            quantity: 0,
            measurement: null,
            serial: null,
            spect: null,
            model: null,
            shelf_localization: null,
            comments: null,
            item: null,
            user: null,
        },
        historyHeaders: [
            {text: 'Item', value: 'tool.item'},
            {text: 'Equipo', value: 'set.name'},
            {text: 'Fecha', value: 'created_at'},
            {text: 'Ejecutor', value: 'user.email'},
            {text: 'Actividad', value: 'comment'},
            {text: 'Informacion Actual', value: 'after'},
            {text: 'Informacion Anterior', value: 'before'}
        ]
    }),
    methods: {
        async history() {
            await this.$store.dispatch('filters/setHistoryMode', { value: true })
            this.$emit('loading', true)
            const response = await axios.get('/api/history', getToken())
            if (response.status === 200) {
                const items = response.data.map(item => {
                    return {
                        ...item,
                        before: JSON.parse(item.before),
                        after: JSON.parse(item.after)
                    }
                })
                await this.$store.dispatch('filters/setHistoryItems', { items })
            }
            this.$emit('loading', false)
        },
        async search() {
            this.$emit('loading', true)
            await this.$store.dispatch('filters/setHistoryMode', { value: false })
            const query = {}
            const activeFilters = Object.keys(this.filters).filter(filter => this.filters[filter].active)
            for (let key of activeFilters) {
                if (!_.isEmpty(this.filter[key])) {
                    query[key] = this.filter[key]
                }
            }
            const response = await axios.post('/api/search', query, getToken())
            if (response.status === 200) {
                await this.$store.dispatch('filters/setItems', { items: response.data })
            }
            this.$emit('loading', false)
        }
    },
    computed: {
        ...mapGetters('filters', ['filters','activeFilters'])
    },
    created() {
        axios.get('/api/sets', getToken())
            .then(response => {
                this.sets = this.sets.concat(response.data)
                this.filter.set = this.sets[0]
            })
        axios.get('/api/dess', getToken())
            .then(response => {
                this.dess = this.dess.concat(response.data)
                this.filter.des = this.dess[0]
            })
        axios.get('/api/brands', getToken())
            .then(response => {
                this.brands = this.brands.concat(response.data)
                this.filter.brand = this.brands[0]
            })
        axios.get('/api/calibrations', getToken())
            .then(response => {
                this.calibrations = this.calibrations.concat(response.data)
                this.filter.calibration = this.calibrations[0]
            })
        axios.get('/api/locations', getToken())
            .then(response => {
                this.locations = this.locations.concat(response.data)
                this.filter.location = this.locations[0]
            })
        axios.get('/api/users', getToken())
          .then(response => {
              this.users = this.users.concat(response.data)
          })
    },
    components: {
        activeFilters
    }

}
</script>

<style scoped>

</style>
