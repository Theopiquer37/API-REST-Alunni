<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CertificazioniController
{
  private function db() {
    return new MySQLi('my_mariadb', 'root', 'ciccio', 'scuola');
  }

  public function index(Request $request, Response $response, $args){
    $db = $this->db();
    $sql = "SELECT c.*, a.nome AS alunno_nome, a.cognome AS alunno_cognome
            FROM certificazioni c
            LEFT JOIN alunni a ON c.alunno_id = a.id";
    $result = $db->query($sql);
    $results = $result->fetch_all(MYSQLI_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function show(Request $request, Response $response, $args){
    $db = $this->db();

    $stmt = $db->prepare("SELECT c.*, a.nome AS alunno_nome, a.cognome AS alunno_cognome FROM certificazioni c LEFT JOIN alunni a ON c.alunno_id = a.id WHERE c.id = ?");
    $stmt->bind_param("i", $args['id']);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
      $response->getBody()->write(json_encode(["message" => "Not found"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(404);
    }

    $response->getBody()->write(json_encode($data));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function create(Request $request, Response $response, $args){
    $db = $this->db();
    $body = $request->getParsedBody() ?? [];

    $alunno_id = isset($body['alunno_id']) ? intval($body['alunno_id']) : null;
    $titolo = isset($body['titolo']) ? $body['titolo'] : null;
    $votazione = isset($body['votazione']) ? intval($body['votazione']) : null;
    $ente = isset($body['ente']) ? $body['ente'] : null;

    if (!$alunno_id || !$titolo || $votazione === null || !$ente) {
      $response->getBody()->write(json_encode(["message" => "alunno_id, titolo, votazione e ente sono richiesti"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }

    // controlla esistenza alunno
    $chk = $db->prepare("SELECT id FROM alunni WHERE id = ?");
    $chk->bind_param("i", $alunno_id);
    $chk->execute();
    $res = $chk->get_result();
    if (!$res->fetch_assoc()) {
      $response->getBody()->write(json_encode(["message" => "Alunno non trovato"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }

    $stmt = $db->prepare("INSERT INTO certificazioni (alunno_id, titolo, votazione, ente) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $alunno_id, $titolo, $votazione, $ente);
    $stmt->execute();

    $response->getBody()->write(json_encode([
      "message" => "Creato",
      "id" => $db->insert_id
    ]));

    return $response->withHeader("Content-type", "application/json")->withStatus(201);
  }

  public function update(Request $request, Response $response, $args){
    $db = $this->db();
    $body = $request->getParsedBody() ?? [];

    $alunno_id = isset($body['alunno_id']) ? intval($body['alunno_id']) : null;
    $titolo = isset($body['titolo']) ? $body['titolo'] : null;
    $votazione = isset($body['votazione']) ? intval($body['votazione']) : null;
    $ente = isset($body['ente']) ? $body['ente'] : null;

    if (!$alunno_id || !$titolo || $votazione === null || !$ente) {
      $response->getBody()->write(json_encode(["message" => "alunno_id, titolo, votazione e ente sono richiesti"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }

    // controlla esistenza alunno
    $chk = $db->prepare("SELECT id FROM alunni WHERE id = ?");
    $chk->bind_param("i", $alunno_id);
    $chk->execute();
    $res = $chk->get_result();
    if (!$res->fetch_assoc()) {
      $response->getBody()->write(json_encode(["message" => "Alunno non trovato"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(400);
    }

    $stmt = $db->prepare("UPDATE certificazioni SET alunno_id = ?, titolo = ?, votazione = ?, ente = ? WHERE id = ?");
    $stmt->bind_param("isisi", $alunno_id, $titolo, $votazione, $ente, $args['id']);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
      // può essere che id non esista
      $response->getBody()->write(json_encode(["message" => "Nessuna riga aggiornata"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(404);
    }

    $response->getBody()->write(json_encode(["message" => "Aggiornato"]));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }

  public function destroy(Request $request, Response $response, $args){
    $db = $this->db();

    $stmt = $db->prepare("DELETE FROM certificazioni WHERE id = ?");
    $stmt->bind_param("i", $args['id']);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
      $response->getBody()->write(json_encode(["message" => "Not found"]));
      return $response->withHeader("Content-type", "application/json")->withStatus(404);
    }

    $response->getBody()->write(json_encode(["message" => "Eliminato"]));
    return $response->withHeader("Content-type", "application/json")->withStatus(200);
  }
}
