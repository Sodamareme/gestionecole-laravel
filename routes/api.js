const express = require('express');
const router = express.Router();
const importApprenantsController = require('../controllers/importApprenantsController');

router.post('/apprenants/import', importApprenantsController.import);

module.exports = router;
