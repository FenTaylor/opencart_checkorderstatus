<?php
class ControllerAccountGuest extends Controller {
	private $error = array();

	public function index() {

		$this->load->language('account/login');
		$this->load->language('account/guest');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/customer');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_login'),
			'href' => $this->url->link('account/login')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title')
		);

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('account/guest/info','',true);

		// Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
		if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false)) {
			$data['redirect'] = $this->request->post['redirect'];
		} elseif (isset($this->session->data['redirect'])) {
			$data['redirect'] = $this->session->data['redirect'];

			unset($this->session->data['redirect']);
		} else {
			$data['redirect'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['order_id'])) {
			$data['order_id'] = $this->request->post['order_id'];
		} else {
			$data['order_id'] = '';
		}		
		
			

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/guest_login', $data));
	}

	public function info() {
		
		$this->load->language('account/login');
		$this->load->language('account/order');
		$this->load->language('account/guest');
		
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/guest')
		);

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/guest');
		
		if (isset($this->request->post['email'])) {
			$data["email"] = $this->db->escape($this->request->post['email']);
		} else {
			$email = '';
		}

		if (isset($this->request->post['order_id'])) {
			$data["order_id"] = (int)$this->request->post['order_id'];
		} else {
			$order_id = '';
		}
		

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
			
		if ($data["email"] && $data["order_id"]) {	

			$order_info = $this->model_account_guest->getOrder($data["order_id"], mb_strtolower($data["email"]));
			
			if ($order_info) {
				
				
				
				$data['order_id'] = $order_info["order_id"];
				$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
				
				if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'zone_code' => $order_info['payment_zone_code'],
					'country'   => $order_info['payment_country']
				);

				$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$data['payment_method'] = $order_info['payment_method'];

				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);

				$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$data['shipping_method'] = $order_info['shipping_method'];

				$this->load->model('catalog/product');
				$this->load->model('tool/upload');
				
				// Products
				$data['products'] = array();

				$products = $this->model_account_guest->getProducts($data["order_id"]);

				foreach ($products as $product) {
					$option_data = array();

					$options = $this->model_account_guest->getOptions($this->request->post['order_id'], $product['order_product_id']);

					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if ($upload_info) {
								$value = $upload_info['name'];
							} else {
								$value = '';
							}
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
						);
					}

					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					if ($product_info) {
						$reorder = $this->url->link('account/order/reorder', 'language=' . $this->config->get('config_language') . '&order_id=' . $order_id . '&order_product_id=' . $product['order_product_id']);
					} else {
						$reorder = '';
					}

					$data['products'][] = array(
						'name'     => $product['name'],
						'model'    => $product['model'],
						'option'   => $option_data,
						'quantity' => $product['quantity'],
						'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
						'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
						'reorder'  => $reorder,
						'return'   => $this->url->link('account/return/add', 'language=' . $this->config->get('config_language') . '&order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'])
					);
				}
			
			
				// Totals
				$data['totals'] = array();

				$totals = $this->model_account_guest->getTotals($data["order_id"]);

				foreach ($totals as $total) {
					$data['totals'][] = array(
						'title' => $total['title'],
						'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
					);
				}

				$data['comment'] = nl2br($order_info['comment']);

				// History
				$data['histories'] = array();

				$results = $this->model_account_guest->getHistories($data["order_id"]);

				foreach ($results as $result) {
					$data['histories'][] = array(
						'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
						'status'     => $result['status'],
						'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
					);
				}
			
				
				$this->response->setOutput($this->load->view('account/guest_order', $data));
				
			} else {			
				$data['error_warning'] = $this->language->get('error_attempts');
				$this->response->setOutput($this->load->view('account/guest_login', $data));			
			}		
			
		} else {			
			$data['error_warning'] = $this->language->get('error_attempts');
			$this->response->setOutput($this->load->view('account/guest_login', $data));
		}	

	}
}
