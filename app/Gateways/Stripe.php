<?php namespace App\Gateways;


use Model as Model;

class Stripe extends Model 
{

	
	public function __construct($key) {
		 parent::__construct(); 
			\Stripe\Stripe::setApiKey($key);
	}
	
	
	
	# This is just to create a customer with a card verification
	// Token is Required to create a customer into stripe. 
	public function CreateAccount($token, $name, $email, $description){
			try {
				$Customer = \Stripe\Customer::create(array(
					"description" => $description,
					"name" => $name,
					"source" => $token,
					"email" => $email
				));
				return ($Customer);					
			} catch (\Stripe\Error\Card $e){
				return ($e->getMessage());
			}
	}

	
	# Retrieve Subscription Information
	public function RetrieveSubscription($str){
		try {
			$subscription = \Stripe\Subscription::retrieve($str);
			return($subscription);
		} catch (\Stripe\Error\Base $e) {
			return ($e->getMessage());
		} 
		

	}


	#Create Subscription 
	# https://stripe.com/docs/api/subscriptions/create   
	// a Customer ID is required - this is retrieve from the CreateAccount method. 
	// a PlanID is required.  This is grabbed from stripe plans (https://dashboard.stripe.com/subscriptions/products) 
	// My Recommendation is to use Method ($this->getPlans)  to get the plans. Stripe doesn't make it easy to get the planid 
	
	public function CreateSubscription($CustID, $PlanID){
		$CustomerSubscription = \Stripe\Subscription::create(array(
		  "customer" => $CustID,
		  "items" => array(
			array(
			  "plan" => $PlanID,  
			  "quantity" => 1,
			)
		  )
		));
		
		return ($CustomerSubscription);
	}
	
	
	# Cancels Subscription 
	// Subscription ID is required
	// https://stripe.com/docs/api/subscriptions/cancel

	public function CancelSubscription($str){
		try {
			$subscription = \Stripe\Subscription::retrieve($str);
			$subscription->cancel();		
			return($subscription);
		} catch (\Stripe\Error\Base $e) {
			return ($e->getMessage());
		} 
	}
	

	# Update Subscription. 
	# subscription code required from stripe. 
	// Provide with a new PlanID  
	// https://stripe.com/docs/api/subscriptions/update
	
	public function UpdateSubscription($SubID, $PlanID){
		$subscription = \Stripe\Subscription::retrieve($SubID);
		
		try {
		$UpdateSubscription = \Stripe\Subscription::update($SubID, [
					  'cancel_at_period_end' => false,
					  'proration_behavior' => 'always_invoice',
					  'items' => [
						[
						  'id' => $subscription->items->data[0]->id,
						  'plan' => $PlanID,
						],
					  ]
					]);
		return($UpdateSubscription);
			
			} catch (\Stripe\Error\Base $e) {
				return ($e->getMessage());
		}
	}
	
	#This get customer information everything about the customer from Stripe
	// Just Need Customer id  which looks like cust_348398439 
	// https://stripe.com/docs/api/customers/retrieve
	public function getCustomerInfo($str){
		$Return = \Stripe\Customer::retrieve($str);
		return $Return;
	}
	
	
	#Retrieve a list of plans
	public function getPlans($limit = 10){
		$Return = \Stripe\Plan::all(["limit" => $limit]);
		return $Return;
	}
	
	
	# Retrieves the Plans detail information 
	public function ProductInfo($planId){
		$Return = \Stripe\Plan::retrieve($planId);
		return $Return; 
	}
	
	
	# Update Credit Card Info 
	// Coming Soon 
	public function UpdateCard($token, $custID){
		try {
				$cu = \Stripe\Customer::update(
				  $custID, // stored in your application
				  [
					'source' => $token // obtained with Checkout
				  ]
				);
				
				return $cu;
				//$Message['Success'] = "Your card details have been updated!";
			  }
			  catch(\Stripe\Error\Card $e) {
				  return ($e->getMessage());
				  // $e->getJsonBody(); // show fulll error message
			  }
	}
	
} // End Class 
