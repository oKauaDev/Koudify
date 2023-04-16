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
	public const ERROR_500_INTERNAL_SERVER_ERROR = 500; // Erro interno do servidor
}