<?php

declare(strict_types=1);

namespace Controllers;

interface Models {
	public const REGEXP_TELEFONE = '/^\([1-9]{2}\) (?:[2-8]|9[1-9])[0-9]{3}-[0-9]{4}$/'; // Regexp para validar telefone
	public const REGEXP_EMAIL = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'; // Regexp para validar email
	public const REGEXP_SENHA_FRACA = '/^(?=.*[a-z])(?=.*[A-Z]).{6,}$/'; // Regexp para validar senhas fracas
	public const REGEXP_SENHA_MEDIA = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/'; // Regexp para validar senhas médias
	public const REGEXP_SENHA_FORTE = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{10,}$/'; // Regexp para validar senhas fortes
	public const REGEXP_NOME = '/^[a-zA-ZÀ-ÖØ-öø-ÿ \'-]+$/'; // Regexp para validar nomes
	public const REGEXP_CPF = '/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/'; // Regexp para validar CPF
	public const REGEXP_CNPJ = '/^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/'; // Regexp para validar CNPJ
	public const REGEXP_PLACA_CARRO = '/^[A-Z]{3}\-\d{4}$/'; // Regexp para validar placa de carro
	public const REGEXP_NUM_CARTAO_CREDITO = '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/'; // Regexp para validar número de cartão de crédito
	public const REGEXP_DATA = '/^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/(19|20)\d{2}$/'; // Regexp para validar data no formato MM/DD/YYYY
	public const REGEXP_CVV = '/^\d{3}$/'; // Regexp para validar CVV de cartão de crédito
	public const REGEXP_CPF_CNPJ = '/^\d{2}\.\d{3}\.\d{3}\/?\d{4}\-?\d{2}$/'; // Regexp para validar CPF ou CNPJ

	// Aqui já vai os erros HTTPS

	public const FEED_200_OK = 200; // OK
	public const FEED_201_CREATED = 201; // Criado
	public const ERROR_204_NO_CONTENT = 204; // Sem conteúdo
	public const ERROR_400_BAD_REQUEST = 400; // Requisição inválida
	public const ERROR_401_UNAUTHORIZED = 401; // Não autorizado
	public const ERROR_403_FORBIDDEN = 403; // Proibido
	public const ERROR_404_NOT_FOUND = 404; // Não encontrado
	public const ERROR_405_METHOD_NOT_ALLOWED = 405; // Método não permitido
	public const ERROR_409_CONFLICT = 409; // Conflito
	public const ERROR_422_UNPROCESSABLE_ENTITY = 422; // Entidade não processável
	public const ERROR_429_TOO_MANY_REQUESTS = 429; // Muitas solicitações
	public const ERROR_500_INTERNAL_SERVER_ERROR = 500; // Erro interno do servidor
	public const ERROR_502_BAD_GATEWAY = 502; // Gateway inválido
	public const ERROR_503_SERVICE_UNAVAILABLE = 503; // Serviço indisponível
	public const ERROR_504_GATEWAY_TIMEOUT = 504; // Tempo de espera do gateway esgotado
}