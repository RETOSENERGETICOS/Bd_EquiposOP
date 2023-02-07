export default {
    namespaced: true,
    state: {
        items: [],
        filters: {
            set: { text: 'Equipo', value: 'set.name' ,active: true, key: 'set' },
            des: { text: 'Descripcion', value: 'des.name', active: true, key: 'des' },
            brand: { text: 'Marca', value: 'brand.name', active: true, key: 'brand' },
            calibration: { text: 'Calibracion', value: 'calibration.name', active: true, key: 'calibration' },
            location: { text: 'Localizacion', value: 'location.name', active: true, key: 'location' },
            quantity: { text: 'Cantidad', value: 'quantity', active: false, key: 'quantity' },
            measurement: { text: 'Medida', value: 'measurement', active: false, key: 'measurement' },
            serialNumber: { text: 'Numero de Serie', value: 'serial_number', active: false, key: 'serial_number' },
            spect: { text: 'Caracteristicas', value: 'spect', active: false, key: 'spect' },
            model: { text: 'Modelo', value: 'model', active: false, key: 'model' },
            shelfLocalization: { text: 'Localizacion 2', value: 'shelf_localization', active: false, key: 'shelf_localization' },
            item: { text: 'Item', value: 'item', active: false, key: 'item' },
            user: { text: 'Usuario', value: 'user.name', active: false, key: 'user_id' },
        },
        historyMode: false,
        historyItems: [],
    },
    mutations: {
        setFilters(state, { filters }) {
            state.filters = filters
        },
        setItems(state, { items }) {
            state.items = items
        },
        setHistoryMode(state, { value }) {
            state.historyMode = value
        },
        setHistoryItems(state, { items }) {
            state.historyItems = items
        }
    },
    actions: {
        setHistoryItems({ commit }, { items }) {
            commit('setHistoryItems', { items })
        },
        setHistoryMode({ commit }, { value }) {
            commit('setHistoryMode', { value })
        },
        setFilters({ commit }, { filters }) {
            commit('setFilters', { filters })
        },
        setItems({ commit }, { items }) {
            commit('setItems', { items })
        }
    },
    getters: {
        historyItems: state => {
            return state.historyItems
        },
        historyMode: state => {
            return state.historyMode
        },
        activeFilters: state => {
            const activeFiltersKeys = Array.from(Object.keys(state.filters)).filter(key => state.filters[key].active)
            return activeFiltersKeys.map(key => state.filters[key])
        },
        filters: state => {
            return JSON.parse(JSON.stringify(state.filters))
        },
        items: state => {
            return state.items
        }
    }
}
