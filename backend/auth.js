app.post('/login', (req, res) => {
    if(req.body.password === process.env.ADMIN_PW) {
      res.cookie('auth', 'club-admin');
      res.redirect('/admin');
    }
  });