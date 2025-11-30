<?php

/**
 * Vérifie si un nom d'utilisateur existe déjà dans la base de données.
 * @param string $username Le nom d'utilisateur à vérifier.
 * @return bool True si l'utilisateur existe, false sinon.
 */
function userExists(string $username): bool {
    $pdo = dbConnect();
    $sql = "SELECT COUNT(*) FROM utilisateur WHERE NOM_USER = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    return $stmt->fetchColumn() > 0; // fetchColumn() retourne la valeur de la première colonne (ici, le COUNT).
}

/**
 * Authentifie un utilisateur en vérifiant son nom d'utilisateur et son mot de passe.
 * @param string $username Le nom d'utilisateur.
 * @param string $password Le mot de passe en clair.
 * @return array|false Les informations de l'utilisateur si l'authentification réussit, sinon false.
 */
function authenticateUser(string $username, string $password): array|false {
    $pdo = dbConnect();
    $sql = "SELECT ID_USER, NOM_USER, EMAIL, PASSWORD FROM utilisateur WHERE NOM_USER = ?"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Vérifie si l'utilisateur a été trouvé et si le mot de passe fourni correspond au hash stocké.
    if ($user && password_verify($password, $user['PASSWORD'])) {
        unset($user['PASSWORD']); // Ne jamais stocker le hash du mot de passe en session.
        return $user; 
    }
    return false;
}

/**
 * Enregistre un nouvel utilisateur dans la base de données.
 * @param string $username Le nom d'utilisateur choisi.
 * @param string $email L'email de l'utilisateur.
 * @param string $password Le mot de passe en clair.
 * @return int|false L'ID du nouvel utilisateur si l'inscription réussit, sinon false.
 */
function registerUser(string $username,string $email, string $password): int|false {
    if (userExists($username)) {
        return false;
    }
    $pdo = dbConnect();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO utilisateur (NOM_USER, EMAIL, PASSWORD) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$username, $email, $passwordHash])) {
        return (int)$pdo->lastInsertId();
    }
    return false;
}

/**
 * Gère la déconnexion de l'utilisateur en détruisant la session.
 */
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}