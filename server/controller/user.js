const userReg = (req, res) => {
  console.log(req.body);
  const { username, name, age } = req.body;
  // Do something with the data
  console.log(username, name, age);
  res.send("User registration");
};

const userController = (req, res) => {
  res.json({
    message: "User controller endpoint",
    timestamp: new Date().toISOString(),
  });
};

module.exports = {
  userReg,
  userController,
};
