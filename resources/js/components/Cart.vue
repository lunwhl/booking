<template>
	<div>
		<ul class="cart">
			<li>
				<a aria-expanded="true" aria-haspopup="true" data-toggle="dropdown" id="cart" class="btn dropdown-toggle" title="Add To Cart" href="#">
					<i class="fa fa-shopping-cart" aria-hidden="true"></i>
					<h3>Shopping Cart</h3>
					<h5>({{numberOfItemInCart}}) Items - <span>RM{{totalPriceInCart}}</span></h5>
				</a>
				<ul class="dropdown-menu no-padding">
					<li v-if="carts" v-for="cart in carts['cart']" class="mini_cart_item">
						<a title="Remove this item" class="remove" style="cursor: pointer;" @click="deleteCart(cart)">&#215;</a>
						<a :href="productUrl(cart)" class="shop-thumbnail">
							<img width="60" height="60" alt="poster_2_up" class="attachment-shop_thumbnail" :src="$options.filters.set_image(cart.image_path)" />{{cart.name}} &#215;
						</a>
						<span class="quantity">{{cart.qty}} &#215; <span class="amount">RM{{totalPriceOfProduct(cart)}}</span></span>
					</li>
					<li class="cart-button">
						<a href="/cart" title="View Cart">View Cart</a>
						<a href="/cart" title="Check Out">Check out</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</template>

<script>
    export default {
    	data() {
    		return {
    			isLoading: true,
    			latestProducts: [],
    			testCart: [],
    			carts: [],
    		};
    	},

        mounted() {
        	window.event.$on("cart-add", () => {
        		this.getCart();
        	});
        	this.getCart();
         //    this.getLatestProduct();
        },

        methods: {   
            productUrl(cart){
                return '/product/' + cart.id;
            },

        	getCart() {
				let url = '/cart/index';
        		axios.post(url)
        		.then(response => this.getCartSuccess(response.data));
        	},

        	getCartSuccess(data) {
        		this.carts = data;
        	},

        	addToCartSuccess(data) {
        		this.getCart();
        	},

        	totalPriceOfProduct(cart){
        		let $total = 0;
        		$total = cart.price * cart.qty;
				return $total;
        	},

            deleteCart(cart){
                let url = '/api/cart/delete/' + cart.rowId;
                axios.post(url)
                .then(response => this.deleteCartSuccess(response.data));
            },

            deleteCartSuccess(data) {
                this.carts = data;
            },
        },

        computed:{
        	numberOfItemInCart(){
        		let $total = 0;
        		_.each(this.carts['cart'], function (cart) {
					$total += cart.qty;
				});
				return $total;
        	},

        	totalPriceInCart(){
        		let $total = 0;
        		_.each(this.carts['cart'], function (cart) {
					$total += cart.qty * cart.price;
				});
				return $total;
        	},

        	
        },
    }
</script>