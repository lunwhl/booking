	<template>
		<v-app>
			<div class="container">
	            <div class="text-center">
	                <div class="simple-article size-3 uppercase col-xs-b5">shopping cart</div>
	                <div class="h2">check your products</div>
	                <div class="title-underline center"><span></span></div>
	            </div>
        	</div>
        	<div class="container">
	            <table class="cart-table">
	                <thead>
	                    <tr>
	                        <th style="width: 95px;"></th>
	                        <th>product name</th>
	                        <th style="width: 150px;">price</th>
	                        <th style="width: 260px;">quantity</th>
	                        <th style="width: 150px;">installation fee</th>
	                        <th style="width: 150px;">total</th>
	                        <th style="width: 70px;"></th>
	                    </tr>
	                </thead>
	                <tbody v-if="isEmptyCart()">
	                    <tr v-for="cart in carts[0]['cart']">
	                        <td data-title=" ">
	                            <a class="cart-entry-thumbnail" href="#"><img :src="$options.filters.set_image(cart.image_path)" alt=""></a>
	                        </td>
	                        <td data-title=" ">
	                        	<h6 class="h6"><a href="#">{{cart.name}}</a></h6>
	                        	<div>
	                        		<label>Installation: </label>
		                        	<select v-model="cart.options['installation']" @change="changeInstallation(cart)" style="border:1px solid #ebebeb; padding:2px 10px;">
		                        		<option value="none">No</option>
		                        		<option value="Economy">Economy</option>
		                        		<option value="Luxury">Luxury</option>
		                        	</select>
	                        	</div>
	                        	<div style="color: red;font-size: 11px;" v-if="checkInstallation(cart)">Selected installation is not available for this product.</div>
	                        </td>
	                        <td data-title="Price: ">RM {{cart.price}}</td>
	                        <td data-title="Quantity: ">
	                            <div class="quantity-select">
	                                <span class="minus" @click="minusQty(cart)"></span>
	                                <span class="number">{{cart.qty}}</span>
	                                <span class="plus" @click="addQty(cart)"></span>
	                            </div>
	                        </td>
	                        <td data-title="Total:">RM {{cart['options']['installationPrice'] * cart.qty}}</td>
	                        <td data-title="Total:">RM {{cart['options']['productTotalPrice'] * cart.qty}}</td>
	                        <td data-title="">
	                            <div @click="deleteCart(cart)" class="button-close"></div>
	                        </td>
	                    </tr>
	                </tbody>
	                <tbody v-else>
		                <tr>
	                    	<td colspan="6">
	                    		<span class="simple-article size-3 uppercase col-xs-b5">Your cart is currently empty. Please click <a href="/shop">here</a> to shop.</span>
	                    	</td>
	                    </tr>
	                </tbody>
	            </table>
            	<div class="empty-space col-xs-b35"></div>
	            <div class="row" v-if="isEmptyCart()">
	                <div class="col-sm-4 col-md-4 col-xs-b10 col-sm-b0 simple-article size-3">
	                	<h4 class="h4">Basic Installation</h4>
	                	*Installation without refrigerant piping provided.
	                	<ul>
	                		<li>Up to 10ft 0.61mm thickness copper piping</li>
	                		<li>Up to 10ft 100% copper cable (without casing)</li>
	                		<ul>
	                			<li>1.0hp & 1.5hp Air Cond - 3 core 1.5mm</li>
	                			<li>1.75hp & above - 4 core 2.5mm</li>
	                		</ul>
	                		<li>Up to 10ft 1/2” thickness premium rubber density insulation</li>
	                		<li>Bracket</li>
	                		<li>1/2” thickness drainage piping</li>
	                		<li>Testing and commissioning of each air cond unit</li>
	                		<li>Electrical wiring interconnecting indoor & outdoor</li>
	                		<li>Vacuum to the system</li>
	                		<li>Refill in refrigerant gas</li>
	                	</ul>
	                </div>
	                <div class="col-sm-4 col-md-4 col-xs-b10 col-sm-b0 simple-article size-3">
	                	<h4 class="h4">Economy Installation</h4>
	                	*Installation with 10ft refrigerant piping provided.
	                	<ul>
	                		<li>No copper piping is included</li>
	                		<li>No wiring cable is included</li>
	                		<li>No insulation is included</li>
	                		<li>Bracket</li>
	                		<li>No drainage piping is included</li>
	                	</ul>
	                </div>
	                <div class="col-sm-4 col-md-4 col-sm-text-right">
	                    <div class="buttons-wrapper">
	                        <a class="button size-2 style-2" @click="updateToCart">
	                            <span class="button-wrapper">
	                                <span class="icon"><img src="css/exzo/img/icon-2.png" alt=""></span>
	                                <span class="text">update cart</span>
	                            </span>
	                        </a>
	                        <a @click="checkout" class="button size-2 style-3" href="#">
	                            <span class="button-wrapper">
	                                <span class="icon"><img src="css/exzo/img/icon-4.png" alt=""></span>
	                                <span class="text">proceed to checkout</span>
	                            </span>
	                        </a>
	                    </div>
	                </div>
	            </div>
            	<div class="empty-space col-xs-b35 col-md-b70"></div>
            	<div class="row" v-if="isEmptyCart()">
	                <div class="col-md-6 col-xs-b50 col-md-b0" >
	                    <h4 class="h4 col-xs-b25">Pick up option</h4>
							<v-radio-group @change="changeRadio" v-model="radios" :mandatory="false">
								<v-radio label="Store Pick Up (*Bring along the invoice for pickup)" value="pickup"></v-radio>
								<v-radio label="P.O Delivery (*Multiple floor shipment will have additional charge)" value="delivery"></v-radio>
							</v-radio-group>
						<div v-if="deliveryNotice">*Multiple floor shipment will have additional charge.*</div>
	                </div>
	                <div class="col-md-6">
	                    <h4 class="h4">cart totals</h4>
	                    <div class="order-details-entry simple-article size-3 grey-border uppercase">
	                        <div class="row">
	                            <div class="col-xs-6">
	                                cart subtotal
	                            </div>
	                            <div class="col-xs-6 col-xs-text-right">
	                                <div class="color">RM {{subTotal}}</div>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="order-details-entry simple-article size-3 grey-border uppercase">
	                        <div class="row">
	                            <div class="col-xs-6">
	                                shipping and handling
	                            </div>
	                            <div class="col-xs-6 col-xs-text-right">
	                                <div class="color">{{deliveryTotal}}</div>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="order-details-entry simple-article size-3 grey-border uppercase">
	                        <div class="row">
	                            <div class="col-xs-6">
	                                order total
	                            </div>
	                            <div class="col-xs-6 col-xs-text-right">
	                                <div class="color">RM {{total}}</div>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
            	<div class="empty-space col-xs-b35 col-md-b70"></div>
    		</div>
    		<alert-login title="Please Login" message="You are required to login in order to proceed to checkout."></alert-login>
    		<login></login>
		</v-app>
	</template>
@stop

<script>
	import AlertLogin from '../components/AlertLogin.vue';
	import Login from '../components/Login.vue';

    export default {
    	components: {
			AlertLogin,
			Login
		},

    	props: ['is_auth'],

    	data() {
    		return {
    			latestProducts: [],
    			categories: [],
    			carts: [],
    			item: {
    				category: []
    			},
    			page: 1,
    			skip: 0,
    			take: 12,
    			url: '',
    			hpMin: 9000,
    			hpMax: 60000,
    			sortSelected: 'asc', 
    			productTotal: 0,
    			products: [],
    			radios: 'pickup',
    			deliveryNotice: false,
    			deliveryCharge: 0,
    		};
    	},

        mounted() {
            this.getCart();
        },

        methods: {
        	isEmptyCart(){
        		if(this.carts.length > 0)
        			if(this.carts[0]['cart'] == null || !this.carts[0]['cart'].length > 0)
        				return false;
        			else
        				return true;
        	},

        	checkout() {
				let url = '/api/home/check/auth';
        		axios.post(url)
        		.then(response => this.checkoutSuccess(response.data));
        	},

        	checkoutSuccess(data)
        	{
        		if(!data['auth'])
        			window.event.$emit("login-alert", {'openDialog': true});
        		else
					window.location.href = "/checkout?shipping="+this.radios;
        	},

        	changeRadio() {
        		if(this.radios == 'delivery'){
        			this.deliveryNotice = true;
        			this.deliveryCharge = 30;
        		} else {
        			this.deliveryNotice = false;
        			this.deliveryCharge = 0;
        		}
        	},

        	checkboxCategory() {
        		Vue.nextTick(()=>{
				    this.getProductForShop();
				}); 
        	},

        	checkInstallation(cart) {
        		if(cart['options']['installationPrice'] == 0 && cart.options['installation'] != 'none') {
        			Vue.set(cart['options'], 'checkInstallation', true);
        			return true;
        		} else {
        			Vue.set(cart['options'], 'checkInstallation', false);
        			return false;
        		}
        	},

        	updateToCart(){
        		this.scrollToTop();
        		_.forEach(this.carts[0]['cart'], function(cart, key) {
        			if(cart['options']['checkInstallation'])
    					cart['options']['installation'] = 'none';
				});
        		let url = '/api/cart/bulk/update';
        		axios.post(url, this.carts)
        		.then(response => this.updateToCartSuccess(response.data));
        	},

        	updateToCartSuccess(data) {
				Vue.nextTick(()=>{
        			if(data['cart'].length > 0)
				    	Vue.set(this.carts, 0, data);
				}); 
				this.addToCartSuccess(data);
        	},

        	changeInstallation(cart) {
        		axios.post('/api/cart/installation?type=' + cart.options['installation'], cart)
        		.then(response => this.changeInstallationSuccesss(response.data, cart));
        	},

        	changeInstallationSuccesss(data, cart) {
        		Vue.nextTick(()=>{
        			Vue.set(cart['options'], 'productTotalPrice', data['installationPrice']);
        			if(data['installationPrice'] > 0)
        				Vue.set(cart['options'], 'installationPrice', data['installationPrice'] - cart.price);
				});
        	},

        	deleteCart(cart){
                let url = '/api/cart/delete/' + cart.rowId;
                axios.post(url)
                .then(response => this.deleteCartSuccess(response.data));
            },

            deleteCartSuccess(data) {
                Vue.nextTick(()=>{
			    	Vue.set(this.carts, 0, data);
				}); 
				this.addToCartSuccess(data);
            },

        	addToCart(product) {
        		let url = '/cart/add/' + product.id;
        		axios.post(url)
        		.then(response => this.addToCartSuccess(response.data));
        	},

        	addToCartSuccess(data) {
        		window.event.$emit("cart-add");
        	},

        	getCart() {
				let url = '/cart/index';
        		axios.post(url)
        		.then(response => this.getCartSuccess(response.data));
        	},

        	getCartSuccess(data) {
    			if(data['cart'].length > 0)
			    	Vue.set(this.carts, 0, data);
        	},

        	minusQty(cart) {
        		if(cart.qty > 1)
        			cart.qty = cart.qty - 1;
        	},

        	addQty(cart) {
    			cart.qty = cart.qty + 1;
        	},

        	scrollToTop() {
                window.scrollTo(0,0);
           }
        },

        computed: {
        	subTotal() {
        		let subTotal = 0;

        		if(this.carts.length > 0)
	        		_.forEach(this.carts[0]['cart'], function(cart, key) {
	        			subTotal += cart['options']['productTotalPrice'] * cart['qty'];
					});

				return subTotal;
        	},

        	total() {
        		let total = 0;
        		return this.subTotal + this.deliveryCharge;
        	},

        	deliveryTotal() {
        		return this.deliveryCharge ? 'RM ' + this.deliveryCharge : 'No Shipping';
        	},
        },
    }
</script>