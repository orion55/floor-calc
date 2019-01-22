<template>
    <div class="calc__form">
        <div v-show="info.loading" class="calc__preloader">
            <div class="calc__loader"></div>
        </div>
        <div v-show="!info.loading" class="calc__box">
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
            <div class="calc__services hvr-pop"> Дополнительные услуги <span class="calc__plus">&#43;</span></div>
            <div class="calc__price">от <span class="calc__sum">1000</span> ₽</div>
            <button type="button" class="btn btn--result hvr-pop"
                    @click.prevent="buttonCheckout.funct"
                    ref="btnCheckout"> Заказать
            </button>
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
    #floor-calc {
        .calc__form {
            position: absolute;
            top: 50%;
            left: 15%;
            width: 290px;
            height: 430px;
            margin: 0;
            padding: 15px;
            background-color: rgba(255, 255, 255, .9);
            box-shadow: 5px 5px 11px rgba(0, 0, 0, .1);
            transform: translateY(-50%);
            z-index: 100;
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

        .multiselect__content {
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
            color: $color-main;
        }

        .calc__head {
            padding: 4px 4px 4px 0;
            font-size: 14px;
        }

        .btn, button.btn {
            display: inline-block;
            text-align: center;
            text-decoration: none;

            margin: 2px 0;

            color: #FFFFFF;
            background-color: $color-main;

            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 0 28px;

            line-height: 40px;
            height: 40px;

            border-radius: 20px;
            transition: all .2s ease;
            border: 0;
            outline: 0;
        }

        .btn:active {
            transform: translateY(1px);
            filter: saturate(150%);
        }

        .btn:hover {
            /*color: #4C4C4C;*/
            background-color: $color-two;
            /*transform: translateY(1px);*/
        }

        .btn::-moz-focus-inner {
            border: none;
        }

        .btn:focus {
            outline: none;
        }

        button.btn--result {
            font-family: Geometria, Arial, sans-serif;
            font-size: 18px;
            background-color: $color-two;
            color: white;
            height: 50px;
            width: 100%;
            margin-top: auto;
            box-shadow: 10px 11px 50px -13px rgba(0, 0, 0, 0.75);
        }
        .calc__box {
            display: flex;
            align-items: left;
            justify-content: flex-start;
            flex-direction: column;
            height: 100%;
            font-family: Geometria, Arial, sans-serif;
        }
        @keyframes hvr-pop {
            50% {
                transform: scale(1.05);
            }
        }
        .hvr-pop {
            display: inline-block;
            vertical-align: middle;
            transform: perspective(1px) translateZ(0);
            box-shadow: 0 0 1px rgba(0, 0, 0, 0);
        }
        .hvr-pop:hover, .hvr-pop:focus, .hvr-pop:active {
            animation-name: hvr-pop;
            animation-duration: 0.4s;
            animation-timing-function: linear;
            animation-iteration-count: 1;
        }
        .calc__services {
            font-size: 15px;
            padding: 10px 10px;
            border: #CDCDCD 1px solid;
            border-radius: 10px;
            margin-top: 7px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: left;
            transition: all .4s ease;
            &:hover {
                border: 1px solid #ee2f7b;
                color: #ee2f7b;
            }
        }
        .calc__plus {
            font-size: 18px;
            margin-left: auto;
            margin-right: 10px;
        }
        .calc__price {
            font-size: 35px;
            padding-top: 7px;
            text-align: right;
            padding-right: 10px;
        }
        .calc__sum {
            color: #ee2f7b;
        }
    }
</style>
