<template>
    <div class="calc__form">
        <div v-show="info.loading" class="calc__preloader">
            <div class="calc__loader"></div>
        </div>
        <div v-show="!info.loading">
            <div class="calc__head">Объект клининга</div>
            <multiselect v-model="objectCleaning.selected" :options="objectCleaning.options"
                         label="name" track-by="id" :searchable="false"
                         :show-labels="false" :maxHeight="200"
                         class="calc__dropdown calc__dropdown--object"
                         :allow-empty="false"></multiselect>
            <div class="calc__head">Количество комнат</div>
            <multiselect v-model="numberOfRooms.selected" :options="numberOfRooms.options"
                         label="name" track-by="id" :searchable="false"
                         :show-labels="false" :maxHeight="200"
                         class="calc__dropdown calc__dropdown--number"
                         :allow-empty="false"></multiselect>
            <div class="calc__head">Периодичность уборки</div>
            <multiselect v-model="periodicity.selected" :options="periodicity.options"
                         label="name" track-by="id" :searchable="false"
                         :show-labels="false" :maxHeight="200"
                         class="calc__dropdown calc__dropdown--periodicity"
                         :allow-empty="false"></multiselect>
            <div class="calc__head">Тип уборки</div>
            <multiselect v-model="cleaningType.selected" :options="cleaningType.options"
                         label="name" track-by="id" :searchable="false"
                         :show-labels="false" :maxHeight="200"
                         class="calc__dropdown calc__dropdown--cleaning"
                         :allow-empty="false"></multiselect>
        </div>
    </div>
</template>

<script>
  import Multiselect from 'vue-multiselect'
  import VueResource from 'vue-resource'

  var _ = require('lodash')

  Vue.component('multiselect', Multiselect)
  Vue.use(VueResource)

  export default {
    name: 'app',
    data () {
      return {
        info: {
          data: null,
          loading: true,
          errored: false,
        },
        objectCleaning: {
          selected: {},
          options: []
        },
        numberOfRooms: {
          selected: {},
          options: []
        },
        periodicity: {
          selected: {},
          options: []
        },
        cleaningType: {
          selected: {},
          options: []
        }
      }
    },
    mounted () {
      this.$http.get(wp_data.plugin_dir_url + 'json/price.json')
        .then(response => {
          this.info.data = response.body
          console.log(this.info.data)

          //Заполняем список объектов уборки
          _.forEach(this.info.data.objectCleaning, (item) => {
            this.objectCleaning.options.push(item)
          })
          this.objectCleaning.selected = this.objectCleaning.options[0]

          this.info.loading = false
        }, error => {
          this.info.loading = false
          console.error(error)
        })
    }
  }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style lang="scss">
    $color-main: #F25E99;
    $color-two: darken($color-main, 10%);

    .calc__form {
        position: absolute;
        top: 50%;
        left: auto;
        width: 290px;
        height: 430px;
        margin: 0;
        padding: 15px;
        background-color: rgba(255, 255, 255, .9);
        box-shadow: 5px 5px 11px rgba(0, 0, 0, .1);
        transform: translateY(-50%);
    }

    .calc__loader {
        border: 5px solid #f3f3f3;
        border-top: 5px solid $color-main;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .calc__preloader {
        display: flex;
        flex: 1;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    #floor-calc .multiselect__content {
        padding-left: 0;
    }

    .multiselect {
        min-height: 33px;
    }

    .multiselect__tags {
        min-height: 33px;
        padding: 3px 33px 0 5px;
        border: 1px solid rgba(60, 60, 60, .26);
    }

    .multiselect__select {
        height: 33px;
    }

    .multiselect__content-wrapper {
        z-index: 40;
    }

    .multiselect__element {
        margin-bottom: 0;
        z-index: 100;
    }

    .multiselect__option--selected.multiselect__option--highlight {
        background: $color-two;
    }

    .multiselect__content {
        margin: 0;
        list-style: none;
        display: inline-block;
        z-index: 100;
    }

    .multiselect__option--highlight {
        background: $color-main;
    }

    .multiselect__select {
        top: 2px;
    }

    .multiselect__single {
        margin-bottom: 0;
        padding-top: 3px;
    }

    .calc__head {
        padding: 5px 5px 5px 0;
    }

</style>
