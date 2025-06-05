<?php
session_start();

// Αν δεν έχει δοθεί έγκυρο ID συνταγής
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$recipe_id = intval($_GET['id']);

include("db_connection.php");
$mysqli = $conn;

// Ανάκτηση στοιχείων συνταγής και δημιουργού
$stmt = $mysqli->prepare("SELECT r.*, u.firstname, u.lastname FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Δεν βρέθηκε η συνταγή.";
    exit();
}

$recipe = $result->fetch_assoc();

// Έλεγχος αν ο χρήστης έχει κάνει like
$has_liked = false;
if (isset($_SESSION['user_id'])) {
    $stmt_check = $mysqli->prepare("SELECT id FROM likes WHERE user_id = ? AND recipe_id = ?");
    $stmt_check->bind_param("ii", $_SESSION['user_id'], $recipe_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $has_liked = $res_check->num_rows > 0;
    $stmt_check->close();
}

// Έλεγχος αν είναι στα αγαπημένα
$has_saved = false;
if (isset($_SESSION['user_id'])) {
    $stmt_saved = $mysqli->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $stmt_saved->bind_param("ii", $_SESSION['user_id'], $recipe_id);
    $stmt_saved->execute();
    $res_saved = $stmt_saved->get_result();
    $has_saved = $res_saved->num_rows > 0;
    $stmt_saved->close();
}

// Υπολογισμός συνολικών likes
$stmt_likes = $mysqli->prepare("SELECT COUNT(*) as like_count FROM likes WHERE recipe_id = ?");
$stmt_likes->bind_param("i", $recipe_id);
$stmt_likes->execute();
$res_likes = $stmt_likes->get_result();
$like_count = $res_likes->fetch_assoc()['like_count'];
?>

<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($recipe['title']); ?></title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("navbar.php"); ?>

<div class="recipe-full-container">
  <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>
  <img src="/recipesweb/<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="Εικόνα Συνταγής" class="recipe-full-img">
  <p><strong>Κατηγορία:</strong> <?php echo htmlspecialchars($recipe['category']); ?></p>
  <p><strong>Από:</strong> <?php echo htmlspecialchars($recipe['firstname'] . " " . $recipe['lastname']); ?></p>
  <p><strong>Ημερομηνία:</strong> <?php echo $recipe['created_at']; ?></p>
  <p class="description"><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>

<?php if (isset($_SESSION['user_id'])): ?>
  <div class="center-text">
    <div class="like-save-wrapper" id="btn-container">
      <button class="like-btn <?php echo $has_liked ? 'liked' : ''; ?>" data-id="<?php echo $recipe_id; ?>">❤️ Μου αρέσει</button>

      <?php if ($has_saved): ?>
        <button class="remove-btn saved" data-id="<?php echo $recipe_id; ?>">🗑️ Αφαίρεση από Αγαπημένα</button>
      <?php else: ?>
        <button class="save-btn" data-id="<?php echo $recipe_id; ?>">💾 Αποθήκευση</button>
      <?php endif; ?>
    </div>

    <div id="like-count" class="like-count">
      <?php 
        if ($has_liked) {
          echo $like_count === 1 
            ? "Αυτή η συνταγή σου άρεσε!"
            : "Σου άρεσε και σε άλλους " . ($like_count - 1) . " χρήστες";
        } else {
          echo $like_count === 1
            ? "1 χρήστης έχει κάνει like"
            : ($like_count > 1 ? $like_count . " χρήστες έχουν κάνει like" : "Κανείς δεν έχει κάνει like ακόμη");
        }
      ?>
    </div>
    <span id="save-message" class="save-message"></span>
  </div>
<?php else: ?>
  <p><a href="#" onclick="openAuthPopup()">Συνδέσου για να μπορέσεις να αλληλεπιδράσεις με αυτή τη συνταγή!</a></p>
<?php endif; ?>

<?php
// Εύρεση προηγούμενης και επόμενης συνταγής
$stmt_next = $mysqli->prepare("SELECT id FROM recipes WHERE id > ? ORDER BY id ASC LIMIT 1");
$stmt_next->bind_param("i", $recipe_id);
$stmt_next->execute();
$res_next = $stmt_next->get_result();
$next_recipe = $res_next->fetch_assoc();
$stmt_next->close();

$stmt_prev = $mysqli->prepare("SELECT id FROM recipes WHERE id < ? ORDER BY id DESC LIMIT 1");
$stmt_prev->bind_param("i", $recipe_id);
$stmt_prev->execute();
$res_prev = $stmt_prev->get_result();
$prev_recipe = $res_prev->fetch_assoc();
$stmt_prev->close();
?>

<div class="prev-next-links">
  <?php if ($prev_recipe): ?>
    <a href="view_recipe.php?id=<?php echo $prev_recipe['id']; ?>" class="btn-nav">← Προηγούμενη</a>
  <?php endif; ?>
  <?php if ($next_recipe): ?>
    <a href="view_recipe.php?id=<?php echo $next_recipe['id']; ?>" class="btn-nav">Επόμενη →</a>
  <?php endif; ?>
</div>

<hr>
<h3>Σχόλια</h3>

<?php
// Σχόλια συνταγής
$stmt_comments = $mysqli->prepare("SELECT c.comment, c.created_at, u.firstname, u.lastname FROM comments c JOIN users u ON c.user_id = u.id WHERE c.recipe_id = ? ORDER BY c.created_at DESC");
$stmt_comments->bind_param("i", $recipe_id);
$stmt_comments->execute();
$res_comments = $stmt_comments->get_result();
while ($row = $res_comments->fetch_assoc()):
?>
  <div class="comment-box">
    <strong><?php echo htmlspecialchars($row['firstname'] . " " . $row['lastname']); ?></strong><br>
    <small><?php echo $row['created_at']; ?></small>
    <p><?php echo nl2br(htmlspecialchars($row['comment'])); ?></p>
  </div>
  <hr>
<?php endwhile; ?>

<?php if (isset($_SESSION['user_id'])): ?>
  <form method="POST" action="comment.php">
    <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
    <textarea name="comment" rows="3" placeholder="Γράψε το σχόλιό σου..." required></textarea>
    <button type="submit">💬 Καταχώρηση σχολίου</button>
  </form>
<?php endif; ?>
</div>

<?php include("footer.php"); ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const likeBtn = document.querySelector(".like-btn");
  const likeCount = document.getElementById("like-count");
  const btnContainer = document.getElementById("btn-container");
  const saveMsg = document.getElementById("save-message");

  if (likeBtn) {
    likeBtn.addEventListener("click", function () {
      const recipeId = this.dataset.id;
      fetch("like_ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `recipe_id=${recipeId}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          likeBtn.classList.toggle("liked");
          likeCount.textContent = data.message;
        }
      });
    });
  }

  function addRemoveButton(recipeId) {
    const removeBtn = document.createElement("button");
    removeBtn.textContent = "🗑️ Αφαίρεση από Αγαπημένα";
    removeBtn.classList.add("remove-btn", "saved");
    removeBtn.setAttribute("data-id", recipeId);

    removeBtn.addEventListener("click", function () {
      fetch("save_favorite_ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `recipe_id=${recipeId}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          removeBtn.remove();
          addSaveButton(recipeId);
          saveMsg.textContent = "Η συνταγή αφαιρέθηκε από τα αγαπημένα σου.";
        }
      });
    });

    btnContainer.appendChild(removeBtn);
  }

  function addSaveButton(recipeId) {
    const saveBtn = document.createElement("button");
    saveBtn.textContent = "💾 Αποθήκευση";
    saveBtn.classList.add("save-btn");
    saveBtn.setAttribute("data-id", recipeId);

    saveBtn.addEventListener("click", function () {
      fetch("save_favorite_ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `recipe_id=${recipeId}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          saveBtn.remove();
          addRemoveButton(recipeId);
          saveMsg.textContent = "Η συνταγή προστέθηκε στα αγαπημένα σου!";
        } else if (data.message === 'already_saved') {
          saveMsg.textContent = "Αυτή η συνταγή υπάρχει ήδη στα αγαπημένα.";
        }
      });
    });

    btnContainer.appendChild(saveBtn);
  }

  const initialSaveBtn = document.querySelector(".save-btn");
  if (initialSaveBtn) {
    const recipeId = initialSaveBtn.dataset.id;
    initialSaveBtn.addEventListener("click", function () {
      fetch("save_favorite_ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `recipe_id=${recipeId}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          initialSaveBtn.remove();
          addRemoveButton(recipeId);
          saveMsg.textContent = "Η συνταγή προστέθηκε στα αγαπημένα σου!";
        } else if (data.message === 'already_saved') {
          saveMsg.textContent = "Αυτή η συνταγή υπάρχει ήδη στα αγαπημένα.";
        }
      });
    });
  }

  const initialRemoveBtn = document.querySelector(".remove-btn");
  if (initialRemoveBtn) {
    const recipeId = initialRemoveBtn.dataset.id;
    initialRemoveBtn.addEventListener("click", function () {
      fetch("remove_favorite_ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `recipe_id=${recipeId}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          initialRemoveBtn.remove();
          addSaveButton(recipeId);
          saveMsg.textContent = "Η συνταγή αφαιρέθηκε από τα αγαπημένα σου.";
        }
      });
    });
  }
});
</script>

</body>
</html>

<?php
$stmt->close();
$stmt_likes->close();
$stmt_comments->close();
$mysqli->close();
?>